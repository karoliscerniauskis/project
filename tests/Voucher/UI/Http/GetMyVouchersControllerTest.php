<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class GetMyVouchersControllerTest extends ApiWebTestCase
{
    public function testGetMyVouchersWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/me/vouchers');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMyVouchersReturnsOnlyVouchersIssuedToOrClaimedByAuthenticatedUser(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'get-my-vouchers-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $userEmail = 'get-my-vouchers-owner@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $userEmail,
            'securePassword123',
        );
        $token = self::login($client, $userEmail, 'securePassword123');
        $otherUserEmail = 'get-my-vouchers-other-user@example.com';
        $providerId = self::createProviderRecord(
            'Get My Vouchers Provider',
            ProviderStatus::Active->value,
        );
        $otherProviderId = self::createProviderRecord(
            'Get My Vouchers Other Provider',
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
            code: 'MY-VOUCHER-ISSUED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $userEmail,
            status: VoucherStatus::Active->value,
        );
        self::createVoucherRecord(
            code: 'MY-VOUCHER-CLAIMED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $userEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $userId,
        );
        self::createVoucherRecord(
            code: 'MY-VOUCHER-OTHER-USER-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $otherUserEmail,
            status: VoucherStatus::Active->value,
        );
        self::createVoucherRecord(
            code: 'MY-VOUCHER-OTHER-PROVIDER-001',
            providerId: $otherProviderId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'other-provider-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'GET',
            '/api/me/vouchers',
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
                'MY-VOUCHER-CLAIMED-001',
                'MY-VOUCHER-ISSUED-001',
            ],
            $codes,
        );

        foreach ($response['data'] as $voucher) {
            self::assertIsArray($voucher);
            self::assertArrayHasKey('id', $voucher);
            self::assertArrayHasKey('code', $voucher);
            self::assertArrayHasKey('providerName', $voucher);
            self::assertIsString($voucher['id']);
            self::assertIsString($voucher['providerName']);
        }
    }

    public function testGetMyVouchersReturnsEmptyArrayWhenUserHasNoVouchers(): void
    {
        $client = self::createClient();
        $email = 'get-my-vouchers-empty@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $email,
            'securePassword123',
        );
        $client->request(
            'GET',
            '/api/me/vouchers',
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
}
