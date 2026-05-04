<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GetProviderUsersControllerTest extends ApiWebTestCase
{
    public function testGetProviderUsersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProviderUsersWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-users-invalid-id@example.com',
            'securePassword123',
        );
        $client->request(
            'GET',
            '/api/providers/invalid-uuid/users',
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

    public function testGetProviderUsersWhenUserIsNotProviderMemberReturnsForbidden(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-users-not-member@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Users Not Member Provider', ProviderStatus::Active->value);
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
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

    public function testGetProviderUsersReturnsEmptyArrayWhenProviderHasNoActiveMembers(): void
    {
        $client = self::createClient();
        $adminEmail = 'get-provider-users-empty-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Users Empty Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
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

    public function testGetProviderUsersReturnsOnlyActiveMembersForSelectedProvider(): void
    {
        $client = self::createClient();
        $adminEmail = 'get-provider-users-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $activeMemberEmail = 'active-member@example.com';
        $activeMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $activeMemberEmail,
            'securePassword123',
        );
        $removedMemberEmail = 'removed-member@example.com';
        $removedMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $removedMemberEmail,
            'securePassword123',
        );
        $otherProviderMemberEmail = 'other-provider-member@example.com';
        $otherProviderMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $otherProviderMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Users Provider', ProviderStatus::Active->value);
        $otherProviderId = self::createProviderRecord('Other Users Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $activeMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $removedMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Removed->value,
        );
        self::createProviderUserRecord(
            providerId: $otherProviderId,
            userId: $otherProviderMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
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

        $providerUser = $response['data'][0];

        self::assertIsArray($providerUser);
        self::assertArrayHasKey('id', $providerUser);
        self::assertArrayHasKey('email', $providerUser);
        self::assertArrayHasKey('role', $providerUser);
        self::assertArrayHasKey('status', $providerUser);
        self::assertIsString($providerUser['id']);
        self::assertSame($activeMemberEmail, $providerUser['email']);
        self::assertSame(ProviderUserRole::Member->value, $providerUser['role']);
        self::assertSame(ProviderUserStatus::Active->value, $providerUser['status']);
    }

    public function testGetProviderUsersAsMemberReturnsUsers(): void
    {
        $client = self::createClient();
        $memberEmail = 'get-provider-users-member-auth@example.com';
        $memberUserId = self::registerVerifyAndGetUserId(
            $client,
            $memberEmail,
            'securePassword123',
        );
        $token = self::login($client, $memberEmail, 'securePassword123');
        $anotherMemberEmail = 'get-provider-users-another-member@example.com';
        $anotherMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $anotherMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Users Member Access Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $memberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $anotherMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
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
            $anotherMemberEmail,
            $memberEmail,
        ], $emails);
    }

    public function testGetProviderUsersWhenProviderUserIsRemovedReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'get-provider-users-removed-auth@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Users Removed Auth Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Removed->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/users', $providerId),
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
