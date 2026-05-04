<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class VerifyUserEmailControllerTest extends ApiWebTestCase
{
    public function testVerifyEmailWithValidSlugSuccessfully(): void
    {
        $client = self::createClient();
        $email = 'verify@example.com';
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $emailVerificationSlug = self::getEmailVerificationSlug($email);
        $client->request(
            'GET',
            sprintf('/api/auth/verify-email/%s', $emailVerificationSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $userRecord = self::getUserRecord($email);

        self::assertNull($userRecord->getEmailVerificationSlug());
        self::assertNotNull($userRecord->getEmailVerifiedAt());
    }

    public function testVerifyEmailWithInvalidSlugReturnsNoContent(): void
    {
        $client = self::createClient();
        $invalidSlug = 'invalid-slug-that-does-not-exist';
        $client->request(
            'GET',
            sprintf('/api/auth/verify-email/%s', $invalidSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Verification link is invalid.',
                'errors' => [
                    [
                        'field' => 'emailVerificationSlug',
                        'message' => sprintf('Verification link "%s" is invalid or expired.', $invalidSlug),
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testVerifyEmailMultipleTimesWithSameSlugReturnsNoContent(): void
    {
        $client = self::createClient();
        $email = 'verify-multiple@example.com';
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $emailVerificationSlug = self::getEmailVerificationSlug($email);
        $client->request(
            'GET',
            sprintf('/api/auth/verify-email/%s', $emailVerificationSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request(
            'GET',
            sprintf('/api/auth/verify-email/%s', $emailVerificationSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Verification link is invalid.',
                'errors' => [
                    [
                        'field' => 'emailVerificationSlug',
                        'message' => sprintf('Verification link "%s" is invalid or expired.', $emailVerificationSlug),
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }
}
