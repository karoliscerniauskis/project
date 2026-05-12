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

final class GetAvailableProvidersToLinkControllerTest extends ApiWebTestCase
{
    public function testGetAvailableProvidersToLinkWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/providers/%s/available-to-link', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetAvailableProvidersToLinkAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'available-to-link-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Available To Link Member Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'GET',
            sprintf('/api/providers/%s/available-to-link', $providerId),
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

    public function testGetAvailableProvidersToLinkReturnsOnlyActiveAdminProvidersThatAreNotAlreadyLinked(): void
    {
        $client = self::createClient();
        $email = 'available-to-link-admin@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $sourceProviderId = self::createProviderRecord('Source Provider', ProviderStatus::Active->value);
        $availableProviderId = self::createProviderRecord('Available Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Already Linked Provider', ProviderStatus::Active->value);
        $memberOnlyProviderId = self::createProviderRecord('Member Only Provider', ProviderStatus::Active->value);
        $inactiveProviderId = self::createProviderRecord('Inactive Provider', ProviderStatus::Inactive->value);

        self::createProviderUserRecord(
            providerId: $sourceProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $availableProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $linkedProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $memberOnlyProviderId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $inactiveProviderId,
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
        $entityManager->flush();

        $client->request(
            'GET',
            sprintf('/api/providers/%s/available-to-link', $sourceProviderId),
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
        self::assertSame($availableProviderId, $provider['id']);
        self::assertSame('Available Provider', $provider['name']);
        self::assertSame(ProviderStatus::Active->value, $provider['status']);
    }

    public function testGetAvailableProvidersToLinkReturnsEmptyArrayWhenNoProvidersAreAvailable(): void
    {
        $client = self::createClient();
        $email = 'available-to-link-empty@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $sourceProviderId = self::createProviderRecord('Only Source Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $sourceProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'GET',
            sprintf('/api/providers/%s/available-to-link', $sourceProviderId),
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
