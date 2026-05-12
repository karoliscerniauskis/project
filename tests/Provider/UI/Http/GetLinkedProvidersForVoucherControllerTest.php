<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetLinkedProvidersForVoucherControllerTest extends ApiWebTestCase
{
    public function testGetLinkedProvidersForVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/linked-providers', $voucherId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetLinkedProvidersForVoucherWithInvalidVoucherIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'linked-providers-for-voucher-invalid-id@example.com',
            'securePassword123',
        );

        $client->request(
            'GET',
            '/api/vouchers/invalid-uuid/linked-providers',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Voucher ID "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetLinkedProvidersForNonExistingVoucherReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'linked-providers-for-voucher-not-found@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/linked-providers', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetLinkedProvidersForVoucherNotClaimedByAuthenticatedUserReturnsNotFound(): void
    {
        $client = self::createClient();
        $ownerEmail = 'linked-providers-for-voucher-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $otherUserEmail = 'linked-providers-for-voucher-other-user@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $otherUserEmail,
            'securePassword123',
        );

        $providerMemberEmail = 'linked-providers-for-voucher-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Linked Providers Voucher Source Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'LINKED-PROVIDERS-VOUCHER-NOT-OWNED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/linked-providers', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetLinkedProvidersForVoucherReturnsOnlyActiveLinkedProviders(): void
    {
        $client = self::createClient();
        $ownerEmail = 'linked-providers-for-voucher-success-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'linked-providers-for-voucher-success-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );

        $providerId = self::createProviderRecord('Voucher Linked Providers Source', ProviderStatus::Active->value);
        $forwardProviderId = self::createProviderRecord('Voucher Forward Linked Provider', ProviderStatus::Active->value);
        $reverseProviderId = self::createProviderRecord('Voucher Reverse Linked Provider', ProviderStatus::Active->value);
        $inactiveProviderId = self::createProviderRecord('Voucher Inactive Linked Provider', ProviderStatus::Inactive->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        self::createProviderLinkRecord($providerId, $forwardProviderId);
        self::createProviderLinkRecord($reverseProviderId, $providerId);
        self::createProviderLinkRecord($providerId, $inactiveProviderId);

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'LINKED-PROVIDERS-VOUCHER-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/linked-providers', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('data', $response);
        self::assertIsArray($response['data']);
        self::assertCount(2, $response['data']);

        $providersByName = [];

        foreach ($response['data'] as $provider) {
            self::assertIsArray($provider);
            self::assertArrayHasKey('id', $provider);
            self::assertArrayHasKey('name', $provider);
            self::assertArrayHasKey('status', $provider);
            self::assertIsString($provider['name']);

            $providersByName[$provider['name']] = $provider;
        }

        self::assertArrayHasKey('Voucher Forward Linked Provider', $providersByName);
        self::assertArrayHasKey('Voucher Reverse Linked Provider', $providersByName);
        self::assertArrayNotHasKey('Voucher Inactive Linked Provider', $providersByName);

        self::assertSame($forwardProviderId, $providersByName['Voucher Forward Linked Provider']['id']);
        self::assertSame(ProviderStatus::Active->value, $providersByName['Voucher Forward Linked Provider']['status']);

        self::assertSame($reverseProviderId, $providersByName['Voucher Reverse Linked Provider']['id']);
        self::assertSame(ProviderStatus::Active->value, $providersByName['Voucher Reverse Linked Provider']['status']);
    }

    public function testGetLinkedProvidersForVoucherReturnsEmptyArrayWhenVoucherProviderHasNoLinks(): void
    {
        $client = self::createClient();
        $ownerEmail = 'linked-providers-for-voucher-empty-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'linked-providers-for-voucher-empty-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Voucher No Links Source Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'LINKED-PROVIDERS-VOUCHER-EMPTY-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/linked-providers', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    private static function createProviderLinkRecord(string $providerId, string $linkedProviderId): void
    {
        $entityManager = self::getEntityManager();
        $entityManager->persist(new ProviderLinkRecord(
            self::getUuidCreator()->create(),
            $providerId,
            $linkedProviderId,
            new DateTimeImmutable(),
        ));
        $entityManager->flush();
    }
}
