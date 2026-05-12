<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GetProviderControllerTest extends ApiWebTestCase
{
    public function testGetProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProviderWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-invalid-id@example.com',
            'securePassword123',
        );
        $client->request(
            'GET',
            '/api/providers/invalid-uuid',
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

    public function testGetProviderWithNonExistingProviderReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-non-existing@example.com',
            'securePassword123',
        );
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Provider not found.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderWhenUserIsNotProviderMemberReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-provider-not-member@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Not Member Provider', ProviderStatus::Active->value);
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Provider not found.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderAsMemberReturnsProvider(): void
    {
        $client = self::createClient();
        $email = 'get-provider-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Member Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [
                    'id' => $providerId,
                    'name' => 'Member Provider',
                    'status' => ProviderStatus::Active->value,
                    'isAdmin' => false,
                    'claimReminderAfterDays' => null,
                    'expiryReminderBeforeDays' => null,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderAsAdminReturnsProviderWithIsAdminTrue(): void
    {
        $client = self::createClient();
        $email = 'get-provider-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Admin Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [
                    'id' => $providerId,
                    'name' => 'Admin Provider',
                    'status' => ProviderStatus::Active->value,
                    'isAdmin' => true,
                    'claimReminderAfterDays' => null,
                    'expiryReminderBeforeDays' => null,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderWhenProviderUserIsRemovedReturnsNotFound(): void
    {
        $client = self::createClient();
        $email = 'get-provider-removed-user@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Removed User Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Removed->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Provider not found.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }
}
