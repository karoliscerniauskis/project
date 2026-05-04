<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ChangeUserEmailControllerTest extends ApiWebTestCase
{
    public function testChangeEmailWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/change-email',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'newEmail' => 'newemail@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testChangeEmailWithInvalidEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = $this->registerVerifyAndLoginUser(
            $client,
            'change-email-invalid@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-email',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newEmail' => 'invalid-email',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'newEmail',
                        'message' => 'New email must be valid.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeEmailWithMissingNewEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-email-missing@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-email',
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
                        'field' => 'newEmail',
                        'message' => 'New email is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeEmailWithInvalidJsonReturnsError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-email-invalid-json@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-email',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            'invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid JSON payload.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeEmailWithExistingEmailReturnsConflict(): void
    {
        $client = self::createClient();
        self::registerVerifyAndLoginUser(
            $client,
            'existing-change-email@example.com',
            'securePassword123',
        );
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-email-owner@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-email',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newEmail' => 'existing-change-email@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertSame(
            [
                'message' => 'Email already exists.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email "existing-change-email@example.com" is already registered.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeEmailSuccessfully(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-email-success@example.com',
            'securePassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-email',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newEmail' => 'changed-email@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
    }
}
