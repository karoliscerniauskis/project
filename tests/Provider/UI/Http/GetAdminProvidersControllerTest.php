<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Status\ProviderStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GetAdminProvidersControllerTest extends ApiWebTestCase
{
    public function testGetAdminProvidersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/admin/providers');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetAdminProvidersReturnsProvidersWithPagination(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'admin-providers@example.com',
            'securePassword123',
        );

        $activeProviderId = self::createProviderRecord('Admin Active Provider', ProviderStatus::Active->value);
        $pendingProviderId = self::createProviderRecord('Admin Pending Provider', ProviderStatus::Pending->value);

        $client->request(
            'GET',
            '/api/admin/providers',
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

        $providersByName = [];

        foreach ($response['data'] as $provider) {
            self::assertIsArray($provider);
            self::assertArrayHasKey('id', $provider);
            self::assertArrayHasKey('name', $provider);
            self::assertArrayHasKey('status', $provider);
            self::assertArrayHasKey('isAdmin', $provider);

            self::assertIsString($provider['name']);

            $providersByName[$provider['name']] = $provider;
        }

        self::assertSame($activeProviderId, $providersByName['Admin Active Provider']['id']);
        self::assertSame(ProviderStatus::Active->value, $providersByName['Admin Active Provider']['status']);
        self::assertTrue($providersByName['Admin Active Provider']['isAdmin']);

        self::assertSame($pendingProviderId, $providersByName['Admin Pending Provider']['id']);
        self::assertSame(ProviderStatus::Pending->value, $providersByName['Admin Pending Provider']['status']);
        self::assertTrue($providersByName['Admin Pending Provider']['isAdmin']);

        self::assertArrayHasKey('pagination', $response);
        self::assertIsArray($response['pagination']);
        self::assertSame(1, $response['pagination']['page']);
        self::assertSame(10, $response['pagination']['limit']);
        self::assertSame(2, $response['pagination']['total']);
        self::assertSame(1, $response['pagination']['totalPages']);
    }

    public function testGetAdminProvidersCanFilterByName(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'admin-providers-filter-name@example.com',
            'securePassword123',
        );

        self::createProviderRecord('Visible Admin Provider', ProviderStatus::Active->value);
        self::createProviderRecord('Hidden Admin Provider', ProviderStatus::Active->value);

        $client->request(
            'GET',
            '/api/admin/providers?name=Visible',
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
        self::assertSame('Visible Admin Provider', $provider['name']);
    }

    public function testGetAdminProvidersCanFilterByStatus(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'admin-providers-filter-status@example.com',
            'securePassword123',
        );

        self::createProviderRecord('Active Admin Provider', ProviderStatus::Active->value);
        self::createProviderRecord('Pending Admin Provider', ProviderStatus::Pending->value);

        $client->request(
            'GET',
            sprintf('/api/admin/providers?status=%s', ProviderStatus::Pending->value),
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
        self::assertSame('Pending Admin Provider', $provider['name']);
        self::assertSame(ProviderStatus::Pending->value, $provider['status']);
    }
}
