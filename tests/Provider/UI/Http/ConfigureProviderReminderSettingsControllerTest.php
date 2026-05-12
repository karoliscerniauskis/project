<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ConfigureProviderReminderSettingsControllerTest extends ApiWebTestCase
{
    public function testConfigureProviderReminderSettingsWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/providers/%s/reminder-settings', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'claimReminderAfterDays' => 3,
                'expiryReminderBeforeDays' => 7,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testConfigureProviderReminderSettingsWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'provider-reminder-settings-invalid-id@example.com',
            'securePassword123',
        );

        $client->request(
            'PATCH',
            '/api/providers/invalid-uuid/reminder-settings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'claimReminderAfterDays' => 3,
                'expiryReminderBeforeDays' => 7,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Provider "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testConfigureProviderReminderSettingsWithInvalidDaysReturnsValidationError(): void
    {
        $client = self::createClient();
        $email = 'provider-reminder-settings-invalid-days@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Reminder Settings Invalid Days Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'PATCH',
            sprintf('/api/providers/%s/reminder-settings', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'claimReminderAfterDays' => 0,
                'expiryReminderBeforeDays' => -1,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'claimReminderAfterDays',
                        'message' => 'Claim reminder days must be positive.',
                    ],
                    [
                        'field' => 'expiryReminderBeforeDays',
                        'message' => 'Expiry reminder days must be positive.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testConfigureProviderReminderSettingsAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'provider-reminder-settings-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Reminder Settings Member Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'PATCH',
            sprintf('/api/providers/%s/reminder-settings', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'claimReminderAfterDays' => 3,
                'expiryReminderBeforeDays' => 7,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(
            [
                'message' => 'Forbidden.',
                'errors' => [
                    [
                        'field' => 'provider',
                        'message' => 'Provider administrator role is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testConfigureProviderReminderSettingsSuccessfullyUpdatesProvider(): void
    {
        $client = self::createClient();
        $email = 'provider-reminder-settings-success@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord('Reminder Settings Success Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'PATCH',
            sprintf('/api/providers/%s/reminder-settings', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'claimReminderAfterDays' => 3,
                'expiryReminderBeforeDays' => 7,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $provider = self::getProviderByName('Reminder Settings Success Provider');

        self::assertSame(3, $provider->getClaimReminderAfterDays());
        self::assertSame(7, $provider->getExpiryReminderBeforeDays());
    }

    public function testConfigureProviderReminderSettingsSuccessfullyClearsProviderSettings(): void
    {
        $client = self::createClient();
        $email = 'provider-reminder-settings-clear@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecordWithReminderSettings(
            'Reminder Settings Clear Provider',
            ProviderStatus::Active->value,
            3,
            7,
        );

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'PATCH',
            sprintf('/api/providers/%s/reminder-settings', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'claimReminderAfterDays' => null,
                'expiryReminderBeforeDays' => null,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $provider = self::getProviderByName('Reminder Settings Clear Provider');

        self::assertNull($provider->getClaimReminderAfterDays());
        self::assertNull($provider->getExpiryReminderBeforeDays());
    }
}
