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

final class UnlinkProviderControllerTest extends ApiWebTestCase
{
    public function testUnlinkProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $linkedProviderId = self::getUuidCreator()->create();

        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/link/%s', $providerId, $linkedProviderId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUnlinkProviderAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'unlink-provider-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('Unlink Source Member Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Unlink Target Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        self::createProviderLinkRecord($providerId, $linkedProviderId);

        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/link/%s', $providerId, $linkedProviderId),
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

        self::assertSame(1, self::countProviderLinks());
    }

    public function testUnlinkProviderSuccessfullyRemovesProviderLink(): void
    {
        $client = self::createClient();
        $email = 'unlink-provider-success@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('Unlink Source Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Unlink Target Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        self::createProviderLinkRecord($providerId, $linkedProviderId);

        self::assertSame(1, self::countProviderLinks());

        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/link/%s', $providerId, $linkedProviderId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertSame(0, self::countProviderLinks());
    }

    private static function createProviderLinkRecord(string $providerId, string $linkedProviderId): void
    {
        $entityManager = self::getEntityManager();
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $providerId,
            $linkedProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->flush();
    }

    private static function countProviderLinks(): int
    {
        return self::getEntityManager()
            ->getRepository(ProviderLinkRecord::class)
            ->count([]);
    }
}
