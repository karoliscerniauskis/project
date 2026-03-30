<?php

declare(strict_types=1);

namespace App\Tests\Auth\Domain\Entity;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testRegisterCreatesUserWithExpectedState(): void
    {
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $id = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = 'user@example.com';
        $hashedPassword = 'password';
        $roles = ['ROLE_USER'];
        $emailVerificationSlug = 'slug';
        $user = User::register(
            $id,
            $email,
            $hashedPassword,
            $roles,
            $emailVerificationSlug,
            $occurredOn,
        );

        self::assertSame($id, $user->getId());
        self::assertNull($user->getPendingEmail());
        self::assertSame($email, $user->getEmail());
        self::assertSame($hashedPassword, $user->getHashedPassword());
        self::assertSame($roles, $user->getRoles());
        self::assertSame($emailVerificationSlug, $user->getEmailVerificationSlug());
        self::assertNull($user->getEmailVerifiedAt());

        $events = $user->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(UserRegistered::class, $events[0]);

        /** @var UserRegistered $event */
        $event = $events[0];

        self::assertSame($email, $event->getEmail());
        self::assertSame($emailVerificationSlug, $event->getEmailVerificationSlug());
        self::assertSame($occurredOn, $event->getOccurredOn());
    }

    public function testReconstituteRestoresState(): void
    {
        $emailVerifiedAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $email = 'user@example.com';
        $pendingEmail = 'pending@example.com';
        $id = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $hashedPassword = 'password';
        $roles = ['ROLE_USER'];
        $emailVerificationSlug = 'slug';
        $user = User::reconstitute(
            $id,
            $email,
            $pendingEmail,
            $hashedPassword,
            $roles,
            $emailVerificationSlug,
            $emailVerifiedAt,
        );

        self::assertSame($email, $user->getEmail());
        self::assertSame($pendingEmail, $user->getPendingEmail());
        self::assertSame($hashedPassword, $user->getHashedPassword());
        self::assertSame($roles, $user->getRoles());
        self::assertSame($emailVerificationSlug, $user->getEmailVerificationSlug());
        self::assertSame($emailVerifiedAt, $user->getEmailVerifiedAt());
        self::assertCount(0, $user->pullDomainEvents());
    }

    public function testVerifyEmailMovesPendingEmailToEmailAndClearsSlug(): void
    {
        $id = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = 'user@example.com';
        $pendingEmail = 'pending@example.com';
        $hashedPassword = 'password';
        $roles = ['ROLE_USER'];
        $emailVerificationSlug = 'slug';
        $verifiedAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $user = User::reconstitute(
            $id,
            $email,
            $pendingEmail,
            $hashedPassword,
            $roles,
            $emailVerificationSlug,
            null,
        );
        $user->verifyEmail($verifiedAt);

        self::assertSame($pendingEmail, $user->getEmail());
        self::assertNull($user->getPendingEmail());
        self::assertSame($verifiedAt, $user->getEmailVerifiedAt());
        self::assertNull($user->getEmailVerificationSlug());
        self::assertCount(0, $user->pullDomainEvents());
    }

    public function testRequestEmailChangeUpdatesPendingEmailAndRecordsEvent(): void
    {
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $id = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $newEmail = 'new@example.com';
        $email = 'user@example.com';
        $hashedPassword = 'password';
        $roles = ['ROLE_USER'];
        $emailVerificationSlug = 'slug';
        $user = User::reconstitute(
            $id,
            $email,
            null,
            $hashedPassword,
            $roles,
            null,
            null,
        );
        $user->requestEmailChange($newEmail, $emailVerificationSlug, $occurredOn);

        self::assertSame($email, $user->getEmail());
        self::assertSame($newEmail, $user->getPendingEmail());
        self::assertSame($emailVerificationSlug, $user->getEmailVerificationSlug());
        self::assertNull($user->getEmailVerifiedAt());

        $events = $user->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(UserEmailChangeRequested::class, $events[0]);

        /** @var UserEmailChangeRequested $event */
        $event = $events[0];

        self::assertSame($newEmail, $event->getEmail());
        self::assertSame($emailVerificationSlug, $event->getEmailVerificationSlug());
        self::assertSame($occurredOn, $event->getOccurredOn());
    }

    public function testChangePasswordUpdatesHashedPassword(): void
    {
        $id = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = 'user@example.com';
        $hashedPassword = 'password';
        $newHashedPassword = 'newPassword';
        $roles = ['ROLE_USER'];
        $user = User::reconstitute(
            $id,
            $email,
            null,
            $hashedPassword,
            $roles,
            null,
            null,
        );
        $user->changePassword($newHashedPassword);

        self::assertSame($newHashedPassword, $user->getHashedPassword());
        self::assertCount(0, $user->pullDomainEvents());
    }
}
