<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Command\VerifyUserEmail;
use App\Auth\Application\Handler\VerifyUserEmailHandler;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class VerifyUserEmailHandlerTest extends TestCase
{
    public function testInvokeVerifiesUserEmailAndStoresDomainEvents(): void
    {
        $pendingEmail = 'pending@example.com';
        $emailVerificationSlug = 'slug';
        $verifiedAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $userRepository = $this->createMock(UserRepository::class);
        $clock = $this->createMock(Clock::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $user = User::reconstitute(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            'user@example.com',
            $pendingEmail,
            'password',
            ['ROLE_USER'],
            $emailVerificationSlug,
            null,
        );
        $userRepository->expects(self::once())
            ->method('findByEmailVerificationSlug')
            ->with($emailVerificationSlug)
            ->willReturn($user);
        $clock->expects(self::once())
            ->method('now')
            ->willReturn($verifiedAt);
        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $user) use ($verifiedAt, $pendingEmail): bool {
                return $user->getEmailVerifiedAt() === $verifiedAt
                    && $user->getEmailVerificationSlug() === null
                    && $user->getPendingEmail() === null
                    && $user->getEmail() === $pendingEmail;
            }));
        $outboxWriter->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events): bool {
                return count($events) === 0;
            }));
        $transactionManager->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new VerifyUserEmailHandler(
            $userRepository,
            $clock,
            $transactionManager,
            $outboxWriter,
        );
        $handler(new VerifyUserEmail($emailVerificationSlug));

        self::assertSame($pendingEmail, $user->getEmail());
        self::assertNull($user->getPendingEmail());
        self::assertSame($verifiedAt, $user->getEmailVerifiedAt());
        self::assertNull($user->getEmailVerificationSlug());
    }
}
