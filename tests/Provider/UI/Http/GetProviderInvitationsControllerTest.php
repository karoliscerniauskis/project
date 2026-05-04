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

final class GetProviderInvitationsControllerTest extends ApiWebTestCase
{
    public function testGetProviderInvitationsWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProviderInvitationsWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-invitations-invalid-id@example.com',
            'securePassword123',
        );
        $client->request(
            'GET',
            '/api/providers/invalid-uuid/invitations',
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

    public function testGetProviderInvitationsWhenUserIsNotProviderMemberReturnsForbidden(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-invitations-not-member@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Invitations Not Member Provider', ProviderStatus::Active->value);
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
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

    public function testGetProviderInvitationsReturnsEmptyArrayWhenProviderHasNoPendingInvitations(): void
    {
        $client = self::createClient();
        $email = 'get-provider-invitations-empty@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Invitations Empty Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderInvitationsReturnsOnlyPendingInvitationsForSelectedProvider(): void
    {
        $client = self::createClient();
        $email = 'get-provider-invitations-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Invitations Provider', ProviderStatus::Active->value);
        $otherProviderId = self::createProviderRecord('Other Invitations Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: 'pending-one@example.com',
            slug: 'pending-one-slug',
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $userId,
        );
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: 'pending-two@example.com',
            slug: 'pending-two-slug',
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $userId,
        );
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: 'accepted@example.com',
            slug: 'accepted-slug',
            status: ProviderInvitationStatus::Accepted->value,
            invitedByUserId: $userId,
            acceptedUserId: $userId,
            acceptedAt: new DateTimeImmutable('-1 hour'),
        );
        self::createProviderInvitationRecord(
            providerId: $otherProviderId,
            email: 'other-provider@example.com',
            slug: 'other-provider-slug',
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $userId,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('data', $response);
        self::assertIsArray($response['data']);
        self::assertCount(2, $response['data']);

        $emails = array_column($response['data'], 'email');

        sort($emails);

        self::assertSame([
            'pending-one@example.com',
            'pending-two@example.com',
        ], $emails);

        foreach ($response['data'] as $invitation) {
            self::assertIsArray($invitation);
            self::assertArrayHasKey('email', $invitation);
            self::assertArrayHasKey('createdAt', $invitation);
            self::assertArrayHasKey('expiresAt', $invitation);
            self::assertIsString($invitation['createdAt']);
            self::assertIsString($invitation['expiresAt']);
        }
    }

    public function testGetProviderInvitationsAsMemberReturnsInvitations(): void
    {
        $client = self::createClient();
        $email = 'get-provider-invitations-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Invitations Member Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: 'member-visible-pending@example.com',
            slug: 'member-visible-pending-slug',
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $userId,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('data', $response);
        self::assertIsArray($response['data']);
        self::assertCount(1, $response['data']);

        $invitation = $response['data'][0];

        self::assertIsArray($invitation);
        self::assertArrayHasKey('email', $invitation);
        self::assertSame('member-visible-pending@example.com', $invitation['email']);
    }

    public function testGetProviderInvitationsWhenProviderUserIsRemovedReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'get-provider-invitations-removed@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Invitations Removed User Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Removed->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/invitations', $providerId),
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
}
