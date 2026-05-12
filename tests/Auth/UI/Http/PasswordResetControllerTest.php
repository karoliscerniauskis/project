<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class PasswordResetControllerTest extends ApiWebTestCase
{
    public function testRequestPasswordResetWithInvalidEmailReturnsValidationError(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/auth/forgot-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'email' => 'invalid-email',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Please provide a valid email address.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRequestPasswordResetForNonExistingEmailReturnsNoContent(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/auth/forgot-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'email' => 'password-reset-non-existing@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testRequestPasswordResetForExistingUserStoresResetToken(): void
    {
        $client = self::createClient();
        $email = 'password-reset-existing@example.com';

        self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );

        $userBeforeRequest = self::getUserRecord($email);

        self::assertNull($userBeforeRequest->getPasswordResetToken());
        self::assertNull($userBeforeRequest->getPasswordResetTokenExpiresAt());

        $client->request(
            'POST',
            '/api/auth/forgot-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'email' => $email,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $userAfterRequest = self::getUserRecord($email);

        self::assertIsString($userAfterRequest->getPasswordResetToken());
        self::assertNotSame('', $userAfterRequest->getPasswordResetToken());
        self::assertInstanceOf(DateTimeImmutable::class, $userAfterRequest->getPasswordResetTokenExpiresAt());
    }

    public function testResetPasswordWithMissingPayloadReturnsValidationError(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/auth/reset-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'resetToken',
                        'message' => 'Reset token is required.',
                    ],
                    [
                        'field' => 'newPassword',
                        'message' => 'New password is required.',
                    ],
                    [
                        'field' => 'newPassword',
                        'message' => 'Password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testResetPasswordWithTooShortPasswordReturnsValidationError(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/auth/reset-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'resetToken' => 'reset-token',
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
                        'message' => 'Password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testResetPasswordWithInvalidTokenReturnsBadRequest(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/auth/reset-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'resetToken' => 'invalid-reset-token',
                'newPassword' => 'newSecurePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid or expired password reset token.',
                'errors' => [
                    [
                        'field' => 'resetToken',
                        'message' => 'The password reset link is invalid or has expired.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testResetPasswordWithExpiredTokenReturnsBadRequest(): void
    {
        $client = self::createClient();
        $email = 'password-reset-expired-token@example.com';
        self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $resetToken = 'expired-reset-token';

        $user = self::getUserRecord($email);
        $user->setPasswordResetToken($resetToken);
        $user->setPasswordResetTokenExpiresAt(new DateTimeImmutable('-1 hour'));
        self::getEntityManager()->flush();

        $client->request(
            'POST',
            '/api/auth/reset-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'resetToken' => $resetToken,
                'newPassword' => 'newSecurePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid or expired password reset token.',
                'errors' => [
                    [
                        'field' => 'resetToken',
                        'message' => 'The password reset link is invalid or has expired.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testResetPasswordWithValidTokenChangesPasswordAndClearsToken(): void
    {
        $client = self::createClient();
        $email = 'password-reset-success@example.com';
        self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $resetToken = 'valid-reset-token';

        $userBeforeReset = self::getUserRecord($email);
        $oldHashedPassword = $userBeforeReset->getHashedPassword();
        $userBeforeReset->setPasswordResetToken($resetToken);
        $userBeforeReset->setPasswordResetTokenExpiresAt(new DateTimeImmutable('+1 hour'));
        self::getEntityManager()->flush();

        $client->request(
            'POST',
            '/api/auth/reset-password',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'resetToken' => $resetToken,
                'newPassword' => 'newSecurePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $userAfterReset = self::getUserRecord($email);

        self::assertNotSame($oldHashedPassword, $userAfterReset->getHashedPassword());
        self::assertNull($userAfterReset->getPasswordResetToken());
        self::assertNull($userAfterReset->getPasswordResetTokenExpiresAt());

        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'email' => $email,
                'password' => 'newSecurePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
