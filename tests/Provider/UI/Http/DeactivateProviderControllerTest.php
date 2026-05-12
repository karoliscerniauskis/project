<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Status\ProviderStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class DeactivateProviderControllerTest extends ApiWebTestCase
{
    public function testDeactivateProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/deactivate', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeactivateProviderAsRegularUserReturnsForbidden(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'deactivate-provider-regular-user@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Regular User Deactivate Provider', ProviderStatus::Active->value);

        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/deactivate', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeactivateProviderWithInvalidProviderIdAsAdminReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'deactivate-provider-invalid-id-admin@example.com',
            'securePassword123',
        );

        $client->request(
            'PATCH',
            '/api/admin/provider/invalid-uuid/deactivate',
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

    public function testDeactivateNonExistingProviderAsAdminReturnsNoContent(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'deactivate-provider-non-existing-admin@example.com',
            'securePassword123',
        );
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/deactivate', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeactivateProviderAsAdminDeactivatesProvider(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'deactivate-provider-admin@example.com',
            'securePassword123',
        );
        self::createProviderRecord('Admin Deactivate Provider', ProviderStatus::Active->value);
        $provider = self::getProviderByName('Admin Deactivate Provider');

        self::assertSame(ProviderStatus::Active->value, $provider->getStatus());

        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/deactivate', $provider->getId()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deactivatedProvider = self::getProviderByName('Admin Deactivate Provider');

        self::assertSame(ProviderStatus::Inactive->value, $deactivatedProvider->getStatus());
    }
}
