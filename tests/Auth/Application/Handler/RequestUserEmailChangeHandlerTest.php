<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Command\RequestUserEmailChange;
use App\Auth\Application\Handler\RequestUserEmailChangeHandler;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class RequestUserEmailChangeHandlerTest extends TestCase
{
    public function testInvokeRequestsEmailChangeAndStoresDomainEvents(): void
    {
        $id = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($id);
        $currentEmail = 'user@example.com';
        $newEmail = 'new@example.com';
        $verificationSlug = 'slug';
        $userRepository = $this->createMock(UserRepository::class);
        $slugGenerator = $this->createMock(EmailVerificationSlugGenerator::class);
        $clock = $this->createMock(Clock::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $user = User::reconstitute(
            $userId,
            $currentEmail,
            null,
            'password',
            ['ROLE_USER'],
            null,
            null,
        );
        $userRepository->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn(null);
        $userRepository->expects(self::once())
            ->method('findByPendingEmail')
            ->with($newEmail)
            ->willReturn(null);
        $slugGenerator->expects(self::once())
            ->method('generate')
            ->willReturn($verificationSlug);
        $clock->expects(self::once())
            ->method('now')
            ->willReturn(new DateTimeImmutable('2020-01-01 00:00:00'));
        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $user) use ($newEmail, $verificationSlug): bool {
                return $user->getPendingEmail() === $newEmail
                    && $user->getEmailVerificationSlug() === $verificationSlug;
            }));
        $outboxWriter->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events): bool {
                if (count($events) !== 1) {
                    return false;
                }

                return $events[0] instanceof UserEmailChangeRequested;
            }));
        $transactionManager->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new RequestUserEmailChangeHandler(
            $userRepository,
            $slugGenerator,
            $clock,
            $transactionManager,
            $outboxWriter,
        );
        $handler(new RequestUserEmailChange($id, $newEmail));

        self::assertSame($newEmail, $user->getPendingEmail());
        self::assertSame($verificationSlug, $user->getEmailVerificationSlug());
        self::assertSame($currentEmail, $user->getEmail());
    }
}
