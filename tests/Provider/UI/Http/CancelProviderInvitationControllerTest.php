<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class CancelProviderInvitationControllerTest extends ApiWebTestCase
{
    public function testCancelProviderInvitationWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/invited.user@example.com', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCancelProviderInvitationAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $userId = self::registerVerifyAndGetUserId(
            $client,
            'cancel-non-admin@example.com',
            'securePassword123',
        );
        $token = self::login($client, 'cancel-non-admin@example.com', 'securePassword123');
        $providerId = self::createProviderRecord('Cancel Non Admin Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/invited.user@example.com', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(
            [
                'message' => 'Forbidden.',
                'errors' => [
                    [
                        'field' => 'provider',
                        'message' => 'You are not allowed to perform this action.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCancelPendingProviderInvitationSuccessfully(): void
    {
        $client = self::createClient();
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            'cancel-admin@example.com',
            'securePassword123',
        );
        $token = self::login($client, 'cancel-admin@example.com', 'securePassword123');
        $providerId = self::createProviderRecord('Cancel Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $invitedEmail = 'cancel-invited@example.com';
        $invitationSlug = 'cancel-provider-invitation-slug';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $adminUserId,
        );
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/%s', $providerId, rawurlencode($invitedEmail)),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $invitation = self::getProviderInvitationBySlug($invitationSlug);

        self::assertSame(ProviderInvitationStatus::Cancelled->value, $invitation->getStatus());
    }

    public function testCancelNonExistingProviderInvitationAsAdminReturnsNoContent(): void
    {
        $client = self::createClient();
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            'cancel-missing-admin@example.com',
            'securePassword123',
        );
        $token = self::login($client, 'cancel-missing-admin@example.com', 'securePassword123');
        $providerId = self::createProviderRecord('Cancel Missing Invitation Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/missing@example.com', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCancelAlreadyAcceptedProviderInvitationDoesNothing(): void
    {
        $client = self::createClient();
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            'cancel-accepted-admin@example.com',
            'securePassword123',
        );
        $token = self::login($client, 'cancel-accepted-admin@example.com', 'securePassword123');
        $acceptedUserId = self::registerVerifyAndGetUserId(
            $client,
            'cancel-accepted-user@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Cancel Accepted Invitation Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $invitedEmail = 'cancel-accepted-user@example.com';
        $invitationSlug = 'cancel-accepted-invitation-slug';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: $invitationSlug,
            status: ProviderInvitationStatus::Accepted->value,
            invitedByUserId: $adminUserId,
            acceptedUserId: $acceptedUserId,
            acceptedAt: new DateTimeImmutable('-1 hour'),
        );
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/%s', $providerId, rawurlencode($invitedEmail)),
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
        self::assertNotNull($invitation->getAcceptedAt());
    }

    public function testCancelProviderInvitationWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'cancel-invalid-provider-id@example.com',
            'securePassword123',
        );
        $client->request(
            'DELETE',
            '/api/providers/invalid-uuid/invitations/invited.user@example.com',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Provider "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCancelProviderInvitationWithMissingEmailReturnsNotFound(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/invitations/', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
