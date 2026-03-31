<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Command\RegisterUser;
use App\Auth\Application\Handler\RegisterUserHandler;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Event\UserRegistered;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class RegisterUserHandlerTest extends TestCase
{
    public function testRegistersUserAndStoresDomainEvents(): void
    {
        $password = 'password';
        $hashedPassword = 'hashedPassword';
        $verificationSlug = 'slug';
        $email = 'user@example.com';
        $roles = ['ROLE_USER'];
        $userRepository = $this->createMock(UserRepository::class);
        $uuidCreator = $this->createMock(UuidCreator::class);
        $clock = $this->createMock(Clock::class);
        $passwordHasher = $this->createMock(UserPasswordHasher::class);
        $slugGenerator = $this->createMock(EmailVerificationSlugGenerator::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $uuidCreator->expects(self::once())
            ->method('create')
            ->willReturn('550e8400-e29b-41d4-a716-446655440000');
        $clock->expects(self::once())
            ->method('now')
            ->willReturn(new DateTimeImmutable('2020-01-01 00:00:00'));
        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with($password)
            ->willReturn($hashedPassword);
        $slugGenerator->expects(self::once())
            ->method('generate')
            ->willReturn($verificationSlug);
        $userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $user) use ($email, $hashedPassword, $verificationSlug, $roles): bool {
                return $user->getEmail() === $email
                    && $user->getHashedPassword() === $hashedPassword
                    && $user->getRoles() === $roles
                    && $user->getEmailVerificationSlug() === $verificationSlug;
            }));
        $outboxWriter->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events): bool {
                if (count($events) !== 1) {
                    return false;
                }

                return $events[0] instanceof UserRegistered;
            }));
        $transactionManager->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new RegisterUserHandler(
            $userRepository,
            $uuidCreator,
            $clock,
            $passwordHasher,
            $slugGenerator,
            $transactionManager,
            $outboxWriter,
        );
        $handler(new RegisterUser($email, $password, $roles));
    }
}
