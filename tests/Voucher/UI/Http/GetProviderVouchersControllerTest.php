<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class GetProviderVouchersControllerTest extends ApiWebTestCase
{
    public function testGetProviderVouchersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'GET',
            sprintf('/api/providers/%s/vouchers', $providerId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProviderVouchersAsNonProviderMemberReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'get-provider-vouchers-non-member@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $email,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Get Provider Vouchers Non Member Provider',
            ProviderStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/vouchers', $providerId),
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
                        'field' => 'voucher',
                        'message' => 'You are not allowed to perform this action.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetProviderVouchersReturnsOnlySelectedProviderVouchers(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'get-provider-vouchers-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $claimedUserEmail = 'get-provider-vouchers-claimed-user@example.com';
        $claimedUserId = self::registerVerifyAndGetUserId(
            $client,
            $claimedUserEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Get Provider Vouchers Provider',
            ProviderStatus::Active->value,
        );
        $otherProviderId = self::createProviderRecord(
            'Get Provider Vouchers Other Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        self::createVoucherRecord(
            code: 'PROVIDER-VOUCHER-ACTIVE-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'provider-voucher-active-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        self::createVoucherRecord(
            code: 'PROVIDER-VOUCHER-USED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $claimedUserEmail,
            status: VoucherStatus::Used->value,
            claimedByUserId: $claimedUserId,
        );
        self::createVoucherRecord(
            code: 'PROVIDER-VOUCHER-OTHER-PROVIDER-001',
            providerId: $otherProviderId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'provider-voucher-other-provider-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/vouchers', $providerId),
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

        $codes = array_column($response['data'], 'code');
        sort($codes);

        self::assertSame(
            [
                'PROVIDER-VOUCHER-ACTIVE-001',
                'PROVIDER-VOUCHER-USED-001',
            ],
            $codes,
        );

        foreach ($response['data'] as $voucher) {
            self::assertIsArray($voucher);
            self::assertArrayHasKey('code', $voucher);
            self::assertArrayHasKey('issuedToEmail', $voucher);
            self::assertArrayHasKey('claimedByUser', $voucher);
            self::assertArrayHasKey('createdByUser', $voucher);
            self::assertArrayHasKey('status', $voucher);
            self::assertIsString($voucher['code']);
            self::assertIsString($voucher['issuedToEmail']);
            self::assertIsString($voucher['createdByUser']);
            self::assertIsString($voucher['status']);
        }
    }

    public function testGetProviderVouchersReturnsEmptyArrayWhenProviderHasNoVouchers(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'get-provider-vouchers-empty-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Get Provider Vouchers Empty Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'GET',
            sprintf('/api/providers/%s/vouchers', $providerId),
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
                'meta' => [
                    'total' => 0,
                    'page' => 1,
                    'perPage' => 20,
                    'totalPages' => 0,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }
}
