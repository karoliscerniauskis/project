<?php

declare(strict_types=1);

namespace App\Tests\Provider\Domain\Entity;

use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProviderInvitationTest extends TestCase
{
    public function testInviteCreatesPendingInvitationAndRecordsEvent(): void
    {
        $id = ProviderInvitationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = 'user@example.com';
        $role = ProviderUserRole::Member;
        $slug = 'slug';
        $invitedByUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $createdAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $expiresAt = new DateTimeImmutable('2020-01-08 00:00:00');
        $invitation = ProviderInvitation::invite(
            $id,
            $providerId,
            $email,
            $role,
            $slug,
            $invitedByUserId,
            $createdAt,
            $expiresAt,
        );

        self::assertSame($id, $invitation->getId());
        self::assertSame($providerId, $invitation->getProviderId());
        self::assertSame($email, $invitation->getEmail());
        self::assertSame($role, $invitation->getRole());
        self::assertSame($slug, $invitation->getSlug());
        self::assertSame(ProviderInvitationStatus::Pending, $invitation->getStatus());
        self::assertSame($invitedByUserId, $invitation->getInvitedByUserId());
        self::assertNull($invitation->getAcceptedUserId());
        self::assertSame($createdAt, $invitation->getCreatedAt());
        self::assertNull($invitation->getAcceptedAt());
        self::assertSame($expiresAt, $invitation->getExpiresAt());

        $events = $invitation->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ProviderInvitationCreated::class, $events[0]);
    }

    public function testReconstituteRestoresState(): void
    {
        $id = ProviderInvitationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = 'user@example.com';
        $role = ProviderUserRole::Member;
        $slug = 'slug';
        $status = ProviderInvitationStatus::Accepted;
        $invitedByUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $acceptedUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440003');
        $createdAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $acceptedAt = new DateTimeImmutable('2020-01-02 00:00:00');
        $expiresAt = new DateTimeImmutable('2020-01-08 00:00:00');
        $invitation = ProviderInvitation::reconstitute(
            $id,
            $providerId,
            $email,
            $role,
            $slug,
            $status,
            $invitedByUserId,
            $acceptedUserId,
            $createdAt,
            $acceptedAt,
            $expiresAt,
        );

        self::assertSame($id, $invitation->getId());
        self::assertSame($providerId, $invitation->getProviderId());
        self::assertSame($email, $invitation->getEmail());
        self::assertSame($role, $invitation->getRole());
        self::assertSame($slug, $invitation->getSlug());
        self::assertSame($status, $invitation->getStatus());
        self::assertSame($invitedByUserId, $invitation->getInvitedByUserId());
        self::assertSame($acceptedUserId, $invitation->getAcceptedUserId());
        self::assertSame($createdAt, $invitation->getCreatedAt());
        self::assertSame($acceptedAt, $invitation->getAcceptedAt());
        self::assertSame($expiresAt, $invitation->getExpiresAt());
    }

    public function testAcceptChangesStatusToAcceptedAndSetsAcceptedData(): void
    {
        $id = ProviderInvitationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = 'user@example.com';
        $role = ProviderUserRole::Member;
        $slug = 'slug';
        $invitedByUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $acceptedUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440003');
        $createdAt = new DateTimeImmutable('2020-01-01 12:00:00');
        $acceptedAt = new DateTimeImmutable('2020-01-02 12:00:00');
        $expiresAt = new DateTimeImmutable('2020-01-08 12:00:00');
        $invitation = ProviderInvitation::invite(
            $id,
            $providerId,
            $email,
            $role,
            $slug,
            $invitedByUserId,
            $createdAt,
            $expiresAt,
        );
        $invitation->accept($acceptedUserId, $acceptedAt);
        $events = $invitation->pullDomainEvents();

        self::assertSame(ProviderInvitationStatus::Accepted, $invitation->getStatus());
        self::assertSame($acceptedUserId, $invitation->getAcceptedUserId());
        self::assertSame($acceptedAt, $invitation->getAcceptedAt());
        self::assertCount(1, $events);
        self::assertInstanceOf(ProviderInvitationCreated::class, $events[0]);
    }

    public function testCancelChangesStatusToCancelledWhenPending(): void
    {
        $id = ProviderInvitationId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = 'user@example.com';
        $role = ProviderUserRole::Member;
        $slug = 'slug';
        $invitedByUserId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $createdAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $expiresAt = new DateTimeImmutable('2020-01-08 00:00:00');
        $invitation = ProviderInvitation::invite(
            $id,
            $providerId,
            $email,
            $role,
            $slug,
            $invitedByUserId,
            $createdAt,
            $expiresAt,
        );
        $invitation->cancel();
        $events = $invitation->pullDomainEvents();

        self::assertSame(ProviderInvitationStatus::Cancelled, $invitation->getStatus());
        self::assertCount(1, $events);
        self::assertInstanceOf(ProviderInvitationCreated::class, $events[0]);
    }
}
