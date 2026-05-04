<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class AcceptProviderInvitationControllerTest extends ApiWebTestCase
{
    public function testAcceptProviderInvitationWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $slug = 'provider-invitation-slug';
        $client->request(
            'POST',
            "/api/provider/invitations/{$slug}/accept",
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAcceptProviderInvitationWithInvalidSlugReturnsNoContent(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'invalid-slug-invitee@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/provider/invitations/invalid-slug/accept',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testAcceptProviderInvitationSuccessfully(): void
    {
        $client = self::createClient();
        $invitedEmail = 'provider-invitee@example.com';
        $invitedUserId = self::registerVerifyAndGetUserId(
            $client,
            $invitedEmail,
            'securePassword123',
        );
        $token = self::login($client, $invitedEmail, 'securePassword123');
        $invitedByUserId = self::registerVerifyAndGetUserId(
            $client,
            'provider-admin@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Accept Test Provider', ProviderStatus::Active->value);
        $invitationSlug = 'accept-provider-invitation-slug';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $invitedByUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/invitations/%s/accept', $invitationSlug),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $invitation = self::getProviderInvitationBySlug($invitationSlug);

        self::assertSame(ProviderInvitationStatus::Accepted->value, $invitation->getStatus());
        self::assertSame($invitedUserId, $invitation->getAcceptedUserId());
        self::assertNotNull($invitation->getAcceptedAt());

        $providerUser = self::getProviderUser($providerId, $invitedUserId);

        self::assertInstanceOf(ProviderUserRecord::class, $providerUser);
        self::assertSame(ProviderUserRole::Member->value, $providerUser->getRole());
        self::assertSame(ProviderUserStatus::Active->value, $providerUser->getStatus());
    }

    public function testAcceptProviderInvitationWithDifferentAuthenticatedUserEmailDoesNothing(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'wrong-user@example.com',
            'securePassword123',
        );
        $invitedByUserId = self::registerVerifyAndGetUserId(
            $client,
            'different-email-admin@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Different Email Provider', ProviderStatus::Active->value);
        $invitationSlug = 'different-email-invitation-slug';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: 'real-invitee@example.com',
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $invitedByUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/invitations/%s/accept', $invitationSlug),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $invitation = self::getProviderInvitationBySlug($invitationSlug);

        self::assertSame(ProviderInvitationStatus::Pending->value, $invitation->getStatus());
        self::assertNull($invitation->getAcceptedUserId());
        self::assertNull($invitation->getAcceptedAt());
    }

    public function testAcceptProviderInvitationForPendingProviderDoesNothing(): void
    {
        $client = self::createClient();
        $invitedEmail = 'pending-provider-invitee@example.com';
        self::registerVerifyAndGetUserId($client, $invitedEmail, 'securePassword123');
        $token = self::login($client, $invitedEmail, 'securePassword123');
        $invitedByUserId = self::registerVerifyAndGetUserId(
            $client,
            'pending-provider-admin@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Pending Provider', ProviderStatus::Pending->value);
        $invitationSlug = 'pending-provider-invitation-slug';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $invitedByUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/invitations/%s/accept', $invitationSlug),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $invitation = self::getProviderInvitationBySlug($invitationSlug);

        self::assertSame(ProviderInvitationStatus::Pending->value, $invitation->getStatus());
        self::assertNull($invitation->getAcceptedUserId());
        self::assertNull($invitation->getAcceptedAt());
    }

    public function testAcceptAlreadyAcceptedProviderInvitationDoesNothing(): void
    {
        $client = self::createClient();
        $invitedEmail = 'already-accepted-invitee@example.com';
        $acceptedUserId = self::registerVerifyAndGetUserId($client, $invitedEmail, 'securePassword123');
        $token = self::login($client, $invitedEmail, 'securePassword123');
        $invitedByUserId = self::registerVerifyAndGetUserId(
            $client,
            'already-accepted-admin@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Already Accepted Provider', ProviderStatus::Active->value);
        $invitationSlug = 'already-accepted-invitation-slug';
        $acceptedAt = new DateTimeImmutable('-1 day');
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Accepted->value,
            invitedByUserId: $invitedByUserId,
            acceptedUserId: $acceptedUserId,
            acceptedAt: $acceptedAt,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/invitations/%s/accept', $invitationSlug),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $invitation = self::getProviderInvitationBySlug($invitationSlug);

        self::assertSame(ProviderInvitationStatus::Accepted->value, $invitation->getStatus());
        self::assertSame($acceptedUserId, $invitation->getAcceptedUserId());
        self::assertSame($acceptedAt->getTimestamp(), $invitation->getAcceptedAt()?->getTimestamp());
    }
}
