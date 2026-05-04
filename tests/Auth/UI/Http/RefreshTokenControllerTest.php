<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Auth\Infrastructure\Doctrine\Entity\RefreshToken;
use App\Tests\ApiWebTestCase;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

final class RefreshTokenControllerTest extends ApiWebTestCase
{
    public function testRefreshTokenWithMissingRefreshTokenReturnsBadRequest(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Missing refresh token.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRefreshTokenWithInvalidRefreshTokenReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'refresh_token' => 'invalid-refresh-token',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(
            [
                'message' => 'Invalid refresh token.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRefreshTokenWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Missing refresh token.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRefreshTokenWithExpiredRefreshTokenReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $email = 'refresh-expired@example.com';
        $refreshTokenValue = 'expired-refresh-token-value';
        self::registerVerifyAndLoginUser($client, $email, 'securePassword123');
        self::createRefreshToken(
            $email,
            $refreshTokenValue,
            new DateTime('-1 hour'),
        );
        $client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'refresh_token' => $refreshTokenValue,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertSame(
            [
                'message' => 'Refresh token expired.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRefreshTokenSuccessfullyReturnsNewAccessToken(): void
    {
        $client = self::createClient();
        $email = 'refresh-success@example.com';
        $refreshTokenValue = 'valid-refresh-token-value';
        self::registerVerifyAndLoginUser($client, $email, 'securePassword123');
        self::createRefreshToken(
            $email,
            $refreshTokenValue,
            new DateTime('+1 hour'),
        );
        $client->request(
            'POST',
            '/api/auth/token/refresh',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'refresh_token' => $refreshTokenValue,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('token', $response);
        self::assertIsString($response['token']);
        self::assertNotSame('', $response['token']);
    }

    private static function createRefreshToken(
        string $email,
        string $refreshTokenValue,
        DateTime $validUntil,
    ): void {
        $refreshToken = new RefreshToken();
        $refreshToken->setUsername($email);
        $refreshToken->setRefreshToken($refreshTokenValue);
        $refreshToken->setValid($validUntil);
        $entityManager = self::getEntityManager();
        $entityManager->persist($refreshToken);
        $entityManager->flush();
    }
}
