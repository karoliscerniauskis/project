<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CreateProviderControllerTest extends ApiWebTestCase
{
    public function testCreateProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'name' => 'Test Provider',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateProviderWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'create-provider-invalid-json@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            '{invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid JSON payload.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCreateProviderWithMissingNameReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'create-provider-missing-name@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'name',
                        'message' => 'Name is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCreateProviderWithEmptyNameReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'create-provider-empty-name@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'name' => '',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'name',
                        'message' => 'Name is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCreateProviderSuccessfully(): void
    {
        $client = self::createClient();
        $email = 'create-provider-success@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'name' => 'Created Provider',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $provider = self::getProviderByName('Created Provider');

        self::assertSame('Created Provider', $provider->getName());
        self::assertSame(ProviderStatus::Pending->value, $provider->getStatus());

        $providerUser = self::getProviderUser($provider->getId(), $userId);

        self::assertInstanceOf(ProviderUserRecord::class, $providerUser);
        self::assertSame(ProviderUserRole::Admin->value, $providerUser->getRole());
        self::assertSame(ProviderUserStatus::Active->value, $providerUser->getStatus());
    }

    public function testCreateProviderWithExistingNameReturnsConflict(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'create-provider-duplicate@example.com',
            'securePassword123',
        );
        self::createProviderRecord('Duplicate Provider', ProviderStatus::Pending->value);
        $client->request(
            'POST',
            '/api/provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'name' => 'Duplicate Provider',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertSame(
            [
                'message' => 'Provider name is already taken.',
                'errors' => [
                    [
                        'field' => 'name',
                        'message' => 'Provider "Duplicate Provider" already exists.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }
}
