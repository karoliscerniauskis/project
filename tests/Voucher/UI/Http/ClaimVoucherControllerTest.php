<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class ClaimVoucherControllerTest extends ApiWebTestCase
{
    public function testClaimVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/claim', $voucherId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testClaimVoucherSuccessfullyAssignsVoucherToUser(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'claim-voucher-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $claimingUserEmail = 'claim-voucher-recipient@example.com';
        $claimingUserId = self::registerVerifyAndGetUserId(
            $client,
            $claimingUserEmail,
            'securePassword123',
        );
        $token = self::login($client, $claimingUserEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Claim Voucher Success Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CLAIM-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $claimingUserEmail,
            status: 'active',
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/claim', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('CLAIM-SUCCESS-001');

        self::assertSame($claimingUserId, $voucher->getClaimedByUserId());
        self::assertSame('active', $voucher->getStatus());
    }

    public function testClaimVoucherIssuedToAnotherEmailReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'claim-voucher-email-mismatch-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $claimingUserEmail = 'claim-voucher-email-mismatch-user@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $claimingUserEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Claim Voucher Email Mismatch Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CLAIM-MISMATCH-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'different-recipient@example.com',
            status: 'active',
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/claim', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        self::assertSame(
            [
                'message' => 'Voucher cannot be claimed by this user.',
                'errors' => [
                    [
                        'field' => 'id',
                        'message' => sprintf('Voucher "%s" is not assigned to this email.', $voucherId),
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );

        $voucher = self::getVoucherByCode('CLAIM-MISMATCH-001');

        self::assertNull($voucher->getClaimedByUserId());
    }

    public function testClaimAlreadyClaimedVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'claim-voucher-already-claimed-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $firstUserEmail = 'claim-voucher-first-owner@example.com';
        $firstUserId = self::registerVerifyAndGetUserId(
            $client,
            $firstUserEmail,
            'securePassword123',
        );
        $secondUserEmail = 'claim-voucher-second-owner@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $secondUserEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Claim Already Claimed Voucher Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CLAIM-ALREADY-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $secondUserEmail,
            status: 'active',
            claimedByUserId: $firstUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/claim', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('CLAIM-ALREADY-001');

        self::assertSame($firstUserId, $voucher->getClaimedByUserId());
    }

    public function testClaimInactiveVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'claim-voucher-inactive-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $claimingUserEmail = 'claim-voucher-inactive-recipient@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $claimingUserEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Claim Inactive Voucher Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherStatus = VoucherStatus::Used->value;
        $voucherId = self::createVoucherRecord(
            code: 'CLAIM-INACTIVE-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $claimingUserEmail,
            status: $voucherStatus,
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/claim', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('CLAIM-INACTIVE-001');

        self::assertNull($voucher->getClaimedByUserId());
        self::assertSame($voucherStatus, $voucher->getStatus());
    }
}
