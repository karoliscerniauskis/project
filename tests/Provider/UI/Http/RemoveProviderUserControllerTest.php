<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RemoveProviderUserControllerTest extends ApiWebTestCase
{
    public function testRemoveProviderUserWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $providerUserId = self::getUuidCreator()->create();
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, $providerUserId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRemoveProviderUserAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $memberEmail = 'remove-provider-user-non-admin@example.com';
        $memberUserId = self::registerVerifyAndGetUserId(
            $client,
            $memberEmail,
            'securePassword123',
        );
        $token = self::login($client, $memberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Remove User Non Admin Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $memberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $memberUserId);
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, $providerUser->getId()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(
            [
                'message' => 'Forbidden.',
                'errors' => [
                    [
                        'field' => 'provider',
                        'message' => 'You are not allowed to perform this action.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testRemoveProviderUserSuccessfullyMarksMemberAsRemoved(): void
    {
        $client = self::createClient();
        $adminEmail = 'remove-provider-user-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $memberEmail = 'remove-provider-user-member@example.com';
        $memberUserId = self::registerVerifyAndGetUserId(
            $client,
            $memberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Remove User Success Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $memberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $memberUserId);
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, $providerUser->getId()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $removedProviderUser = self::getExistingProviderUser($providerId, $memberUserId);

        self::assertSame(ProviderUserRole::Member->value, $removedProviderUser->getRole());
        self::assertSame(ProviderUserStatus::Removed->value, $removedProviderUser->getStatus());
    }

    public function testRemoveProviderUserDoesNotRemoveAdminUser(): void
    {
        $client = self::createClient();
        $adminEmail = 'remove-provider-user-keep-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $anotherAdminEmail = 'remove-provider-user-another-admin@example.com';
        $anotherAdminUserId = self::registerVerifyAndGetUserId(
            $client,
            $anotherAdminEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Remove User Keep Admin Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $anotherAdminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $anotherAdminProviderUser = self::getExistingProviderUser($providerId, $anotherAdminUserId);
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, $anotherAdminProviderUser->getId()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $unchangedProviderUser = self::getExistingProviderUser($providerId, $anotherAdminUserId);

        self::assertSame(ProviderUserRole::Admin->value, $unchangedProviderUser->getRole());
        self::assertSame(ProviderUserStatus::Active->value, $unchangedProviderUser->getStatus());
    }

    public function testRemoveProviderUserWithNonExistingProviderUserIdReturnsNoContent(): void
    {
        $client = self::createClient();
        $adminEmail = 'remove-provider-user-non-existing-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Remove User Non Existing Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, self::getUuidCreator()->create()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testRemoveProviderUserDoesNotRemoveUserFromAnotherProvider(): void
    {
        $client = self::createClient();
        $adminEmail = 'remove-provider-user-cross-provider-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $otherProviderMemberEmail = 'remove-provider-user-cross-provider-member@example.com';
        $otherProviderMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $otherProviderMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Remove User Selected Provider',
            ProviderStatus::Active->value,
        );
        $otherProviderId = self::createProviderRecord(
            'Remove User Other Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $otherProviderId,
            userId: $otherProviderMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $otherProviderUser = self::getExistingProviderUser($otherProviderId, $otherProviderMemberUserId);
        $client->request(
            'DELETE',
            sprintf('/api/providers/%s/users/%s', $providerId, $otherProviderUser->getId()),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $unchangedOtherProviderUser = self::getExistingProviderUser($otherProviderId, $otherProviderMemberUserId);

        self::assertSame(ProviderUserRole::Member->value, $unchangedOtherProviderUser->getRole());
        self::assertSame(ProviderUserStatus::Active->value, $unchangedOtherProviderUser->getStatus());
    }
}
