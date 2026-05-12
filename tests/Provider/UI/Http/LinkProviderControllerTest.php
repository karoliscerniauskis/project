<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class LinkProviderControllerTest extends ApiWebTestCase
{
    public function testLinkProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $linkedProviderId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/providers/%s/link', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'linkedProviderId' => $linkedProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLinkProviderWithInvalidLinkedProviderIdReturnsValidationError(): void
    {
        $client = self::createClient();
        $email = 'link-provider-invalid-linked-provider-id@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $email,
            'securePassword123',
        );
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/providers/%s/link', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'linkedProviderId' => 'invalid-uuid',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'linkedProviderId',
                        'message' => 'Invalid UUID format.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testLinkProviderAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'link-provider-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('Link Source Member Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Link Target Member Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $linkedProviderId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/link', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'linkedProviderId' => $linkedProviderId,
            ]),
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

        self::assertSame(0, self::countProviderLinks());
    }

    public function testLinkProviderWhenUserIsNotAdminOfLinkedProviderReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'link-provider-linked-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('Link Source Admin Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Link Target Non Admin Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $linkedProviderId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/link', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'linkedProviderId' => $linkedProviderId,
            ]),
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

        self::assertSame(0, self::countProviderLinks());
    }

    public function testLinkProviderSuccessfullyCreatesProviderLink(): void
    {
        $client = self::createClient();
        $email = 'link-provider-success@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $providerId = self::createProviderRecord('Link Source Provider', ProviderStatus::Active->value);
        $linkedProviderId = self::createProviderRecord('Link Target Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
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

        $client->request(
            'POST',
            sprintf('/api/providers/%s/link', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'linkedProviderId' => $linkedProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $link = self::getProviderLink($providerId, $linkedProviderId);

        self::assertSame($providerId, $link->getProviderId());
        self::assertSame($linkedProviderId, $link->getLinkedProviderId());
    }

    private static function countProviderLinks(): int
    {
        return self::getEntityManager()
            ->getRepository(ProviderLinkRecord::class)
            ->count([]);
    }

    private static function getProviderLink(string $providerId, string $linkedProviderId): ProviderLinkRecord
    {
        $link = self::getEntityManager()
            ->getRepository(ProviderLinkRecord::class)
            ->findOneBy([
                'providerId' => $providerId,
                'linkedProviderId' => $linkedProviderId,
            ]);

        self::assertInstanceOf(ProviderLinkRecord::class, $link);

        return $link;
    }
}
