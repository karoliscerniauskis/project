<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ChangeUserPasswordControllerTest extends ApiWebTestCase
{
    public function testChangePasswordWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'currentPassword' => 'oldPassword123',
                'newPassword' => 'newPassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testChangePasswordWithShortNewPasswordReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-short@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'currentPassword' => 'currentPassword123',
                'newPassword' => 'short',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'newPassword',
                        'message' => 'New password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangePasswordWithMissingCurrentPasswordReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-missing-current@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newPassword' => 'newPassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'currentPassword',
                        'message' => 'Current password is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangePasswordWithMissingNewPasswordReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-missing-new@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'currentPassword' => 'currentPassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'newPassword',
                        'message' => 'New password is required.',
                    ],
                    [
                        'field' => 'newPassword',
                        'message' => 'New password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangePasswordWithEmptyJsonReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-empty-json@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
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
                        'field' => 'currentPassword',
                        'message' => 'Current password is required.',
                    ],
                    [
                        'field' => 'newPassword',
                        'message' => 'New password is required.',
                    ],
                    [
                        'field' => 'newPassword',
                        'message' => 'New password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangePasswordWithInvalidJsonReturnsError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-invalid-json@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
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

    public function testChangePasswordWithIncorrectCurrentPasswordReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-wrong-current@example.com',
            'correctPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'currentPassword' => 'wrongPassword123',
                'newPassword' => 'newPassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testChangePasswordSuccessfully(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-password-success@example.com',
            'currentPassword123',
        );
        $client->request(
            'POST',
            '/api/auth/change-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'currentPassword' => 'currentPassword123',
                'newPassword' => 'newPassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
