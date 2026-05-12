<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetLinkedProvidersControllerTest extends ApiWebTestCase
{
    public function testGetLinkedProvidersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/providers/%s/linked', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetLinkedProvidersAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'get-linked-providers-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Get Linked Member Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'GET',
            sprintf('/api/providers/%s/linked', $providerId),
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
                        'message' => 'Provider administrator role is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetLinkedProvidersReturnsForwardAndReverseLinkedProviders(): void
    {
        $client = self::createClient();
        $email = 'get-linked-providers-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $sourceProviderId = self::createProviderRecord('Linked Source Provider', ProviderStatus::Active->value);
        $forwardProviderId = self::createProviderRecord('Forward Linked Provider', ProviderStatus::Active->value);
        $reverseProviderId = self::createProviderRecord('Reverse Linked Provider', ProviderStatus::Active->value);
        $inactiveProviderId = self::createProviderRecord('Inactive Linked Provider', ProviderStatus::Inactive->value);

        self::createProviderUserRecord(
            providerId: $sourceProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $entityManager = self::getEntityManager();
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $sourceProviderId,
            $forwardProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $reverseProviderId,
            $sourceProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $sourceProviderId,
            $inactiveProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->flush();

        $client->request(
            'GET',
            sprintf('/api/providers/%s/linked', $sourceProviderId),
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
        self::assertCount(3, $response['data']);

        $providersByName = [];

        foreach ($response['data'] as $provider) {
            self::assertIsArray($provider);
            self::assertArrayHasKey('id', $provider);
            self::assertArrayHasKey('name', $provider);
            self::assertArrayHasKey('status', $provider);
            self::assertIsString($provider['name']);

            $providersByName[$provider['name']] = $provider;
        }

        self::assertArrayHasKey('Forward Linked Provider', $providersByName);
        self::assertArrayHasKey('Reverse Linked Provider', $providersByName);
        self::assertArrayHasKey('Inactive Linked Provider', $providersByName);

        self::assertSame($forwardProviderId, $providersByName['Forward Linked Provider']['id']);
        self::assertSame(ProviderStatus::Active->value, $providersByName['Forward Linked Provider']['status']);

        self::assertSame($reverseProviderId, $providersByName['Reverse Linked Provider']['id']);
        self::assertSame(ProviderStatus::Active->value, $providersByName['Reverse Linked Provider']['status']);

        self::assertSame($inactiveProviderId, $providersByName['Inactive Linked Provider']['id']);
        self::assertSame(ProviderStatus::Inactive->value, $providersByName['Inactive Linked Provider']['status']);
    }

    public function testGetLinkedProvidersReturnsEachLinkedProviderOnlyOnce(): void
    {
        $client = self::createClient();
        $email = 'get-linked-providers-unique-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $sourceProviderId = self::createProviderRecord('Unique Source Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Unique Linked Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $sourceProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $entityManager = self::getEntityManager();
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $sourceProviderId,
            $linkedProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $linkedProviderId,
            $sourceProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->flush();

        $client->request(
            'GET',
            sprintf('/api/providers/%s/linked', $sourceProviderId),
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
        self::assertArrayHasKey('id', $provider);
        self::assertArrayHasKey('name', $provider);
        self::assertArrayHasKey('status', $provider);
        self::assertSame($linkedProviderId, $provider['id']);
        self::assertSame('Unique Linked Provider', $provider['name']);
        self::assertSame(ProviderStatus::Active->value, $provider['status']);
    }

    public function testGetLinkedProvidersReturnsEmptyArrayWhenProviderHasNoLinks(): void
    {
        $client = self::createClient();
        $email = 'get-linked-providers-empty-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('No Links Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'GET',
            sprintf('/api/providers/%s/linked', $providerId),
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
}
