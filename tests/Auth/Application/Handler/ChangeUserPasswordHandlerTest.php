<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Command\ChangeUserPassword;
use App\Auth\Application\Handler\ChangeUserPasswordHandler;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\UserId;
use PHPUnit\Framework\TestCase;

final class ChangeUserPasswordHandlerTest extends TestCase
{
    public function testInvokeChangesUserPasswordAndStoresDomainEvents(): void
    {
        $id = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($id);
        $email = 'user@example.com';
        $newPassword = 'new-password';
        $hashedPassword = 'password';
        $newHashedPassword = 'newPassword';
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasher::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $user = User::reconstitute(
            $userId,
            $email,
            null,
            $hashedPassword,
            ['ROLE_USER'],
            null,
            null,
        );
        $userRepository->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);
        $passwordHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($hashedPassword, $hashedPassword)
            ->willReturn(true);
        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with($newPassword)
            ->willReturn($newHashedPassword);
        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $savedUser) use ($newHashedPassword): bool {
                return $savedUser->getHashedPassword() === $newHashedPassword;
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
        $handler = new ChangeUserPasswordHandler(
            $userRepository,
            $passwordHasher,
            $transactionManager,
            $outboxWriter,
        );
        $handler(new ChangeUserPassword(
            $id,
            $hashedPassword,
            $newPassword,
        ));

        self::assertSame($newHashedPassword, $user->getHashedPassword());
    }
}
