<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Status\ProviderStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApproveProviderControllerTest extends ApiWebTestCase
{
    public function testApproveProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = '019d882d-1d68-7e2f-94ce-0cd2f4d0c369';
        $client->request(
            'PATCH',
            "/api/admin/provider/{$providerId}/approve",
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testApproveProviderAsRegularUserReturnsForbidden(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'approve-regular-user@example.com',
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Regular User Approve Provider', ProviderStatus::Pending->value);
        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/approve', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testApproveNonExistingProviderAsAdminReturnsNoContent(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'approve-non-existing-admin@example.com',
            'securePassword123',
        );
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'PATCH',
            sprintf('/api/admin/provider/%s/approve', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testApproveProviderWithInvalidProviderIdAsAdminReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::createAdminUserAndLogin(
            $client,
            'approve-invalid-id-admin@example.com',
            'securePassword123',
        );
        $client->request(
            'PATCH',
            '/api/admin/provider/invalid-uuid/approve',
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
}
