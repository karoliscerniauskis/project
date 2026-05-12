<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GetProvidersControllerTest extends ApiWebTestCase
{
    public function testGetProvidersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(
            'GET',
            '/api/providers',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProvidersReturnsEmptyArrayForUserWithoutProviders(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'get-providers-empty@example.com',
            'securePassword123',
        );
        $client->request(
            'GET',
            '/api/providers',
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
                'pagination' => [
                    'page' => 1,
                    'limit' => 10,
                    'total' => 0,
                    'totalPages' => 0,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProvidersReturnsProvidersForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $email = 'get-providers-user@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $adminProviderId = self::createProviderRecord('Admin Listed Provider', ProviderStatus::Active->value);
        $memberProviderId = self::createProviderRecord('Member Listed Provider', ProviderStatus::Pending->value);
        self::createProviderUserRecord(
            providerId: $adminProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $memberProviderId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            '/api/providers',
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

        $providersByName = self::indexProvidersByName($response['data']);

        self::assertSame(
            [
                'id' => $adminProviderId,
                'name' => 'Admin Listed Provider',
                'status' => ProviderStatus::Active->value,
                'isAdmin' => true,
                'claimReminderAfterDays' => null,
                'expiryReminderBeforeDays' => null,
            ],
            $providersByName['Admin Listed Provider'],
        );

        self::assertSame(
            [
                'id' => $memberProviderId,
                'name' => 'Member Listed Provider',
                'status' => ProviderStatus::Pending->value,
                'isAdmin' => false,
                'claimReminderAfterDays' => null,
                'expiryReminderBeforeDays' => null,
            ],
            $providersByName['Member Listed Provider'],
        );
    }

    public function testGetProvidersDoesNotReturnOtherUsersProviders(): void
    {
        $client = self::createClient();
        $email = 'get-providers-owner@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            'get-providers-other-user@example.com',
            'securePassword123',
        );
        $ownProviderId = self::createProviderRecord('Own Provider', ProviderStatus::Active->value);
        $otherProviderId = self::createProviderRecord('Other User Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $ownProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $otherProviderId,
            userId: $otherUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            '/api/providers',
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

        $provider = $response['data'][0];

        self::assertIsArray($provider);
        self::assertSame('Own Provider', $provider['name']);
        self::assertSame($ownProviderId, $provider['id']);
    }

    public function testGetProvidersDoesNotReturnRemovedProviderUserProviders(): void
    {
        $client = self::createClient();
        $email = 'get-providers-removed@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $activeProviderId = self::createProviderRecord('Visible Provider', ProviderStatus::Active->value);
        $removedProviderId = self::createProviderRecord('Removed Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $activeProviderId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $removedProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Removed->value,
        );
        $client->request(
            'GET',
            '/api/providers',
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

        $provider = $response['data'][0];

        self::assertIsArray($provider);
        self::assertSame('Visible Provider', $provider['name']);
        self::assertSame($activeProviderId, $provider['id']);
    }

    /**
     * @param mixed[] $providers
     *
     * @return array<string, array{id: string, name: string, status: string, isAdmin: bool}>
     */
    private static function indexProvidersByName(array $providers): array
    {
        $indexed = [];

        foreach ($providers as $provider) {
            self::assertIsArray($provider);
            self::assertArrayHasKey('id', $provider);
            self::assertArrayHasKey('name', $provider);
            self::assertArrayHasKey('status', $provider);
            self::assertArrayHasKey('isAdmin', $provider);
            self::assertIsString($provider['id']);
            self::assertIsString($provider['name']);
            self::assertIsString($provider['status']);
            self::assertIsBool($provider['isAdmin']);
            self::assertTrue($provider['claimReminderAfterDays'] === null || is_int($provider['claimReminderAfterDays']));
            self::assertTrue($provider['expiryReminderBeforeDays'] === null || is_int($provider['expiryReminderBeforeDays']));

            $indexed[$provider['name']] = [
                'id' => $provider['id'],
                'name' => $provider['name'],
                'status' => $provider['status'],
                'isAdmin' => $provider['isAdmin'],
                'claimReminderAfterDays' => $provider['claimReminderAfterDays'],
                'expiryReminderBeforeDays' => $provider['expiryReminderBeforeDays'],
            ];
        }

        return $indexed;
    }
}
