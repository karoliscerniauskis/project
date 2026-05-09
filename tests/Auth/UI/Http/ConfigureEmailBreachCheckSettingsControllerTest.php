<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Http;

use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ConfigureEmailBreachCheckSettingsControllerTest extends ApiWebTestCase
{
    public function testConfigureEmailBreachCheckSettingsWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();

        $client->request(
            'PATCH',
            '/api/me/email-breach-check-settings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'enabled' => true,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEnableEmailBreachCheckSettings(): void
    {
        $client = self::createClient();
        $email = 'email-breach-settings-enable@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $email,
            'securePassword123',
        );

        $client->request(
            'PATCH',
            '/api/me/email-breach-check-settings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'enabled' => true,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $user = self::getUserRecord($email);

        self::assertTrue($user->isEmailBreachCheckEnabled());
    }

    public function testDisableEmailBreachCheckSettings(): void
    {
        $client = self::createClient();
        $email = 'email-breach-settings-disable@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $email,
            'securePassword123',
        );

        $client->request(
            'PATCH',
            '/api/me/email-breach-check-settings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'enabled' => true,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request(
            'PATCH',
            '/api/me/email-breach-check-settings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'enabled' => false,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $user = self::getUserRecord($email);

        self::assertFalse($user->isEmailBreachCheckEnabled());
    }
}
