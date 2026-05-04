<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserControllerTest extends ApiWebTestCase
{
    public function testRegisterUserSuccessfully(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'newuser@example.com',
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testRegisterUserWithInvalidEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'invalid-email',
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email must be valid.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithShortPasswordReturnsValidationError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'user@example.com',
                'password' => 'short',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'password',
                        'message' => 'Password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithMissingEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithMissingPasswordReturnsValidationError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'user@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'password',
                        'message' => 'Password is required.',
                    ],
                    [
                        'field' => 'password',
                        'message' => 'Password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithExistingEmailReturnsConflict(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'existing@example.com',
                'password' => 'securePassword123',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'existing@example.com',
                'password' => 'anotherPassword456',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertSame(
            [
                'message' => 'Email already exists.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email "existing@example.com" is already registered.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithEmptyJsonReturnsValidationError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email is required.',
                    ],
                    [
                        'field' => 'password',
                        'message' => 'Password is required.',
                    ],
                    [
                        'field' => 'password',
                        'message' => 'Password must be at least 8 characters long.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRegisterUserWithInvalidJsonReturnsError(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
