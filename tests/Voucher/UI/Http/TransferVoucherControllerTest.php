<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class TransferVoucherControllerTest extends ApiWebTestCase
{
    public function testTransferVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/transfer', $voucherId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'recipientEmail' => 'new-recipient@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testTransferVoucherWithInvalidRecipientEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $ownerEmail = 'transfer-voucher-invalid-email-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Transfer Voucher Invalid Email Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $ownerUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $ownerUserId);
        $voucherId = self::createVoucherRecord(
            code: 'TRANSFER-INVALID-EMAIL-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/transfer', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'recipientEmail' => 'invalid-email',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'recipientEmail',
                        'message' => 'Email must be valid.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );

        $voucher = self::getVoucherByCode('TRANSFER-INVALID-EMAIL-001');

        self::assertSame($ownerEmail, $voucher->getIssuedToEmail());
    }

    public function testTransferVoucherSuccessfullyChangesIssuedToEmail(): void
    {
        $client = self::createClient();
        $ownerEmail = 'transfer-voucher-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Transfer Voucher Success Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $ownerUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $ownerUserId);
        $voucherId = self::createVoucherRecord(
            code: 'TRANSFER-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
        );
        $recipientEmail = 'transfer-voucher-new-recipient@example.com';
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/transfer', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'recipientEmail' => $recipientEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('TRANSFER-SUCCESS-001');

        self::assertSame($recipientEmail, $voucher->getIssuedToEmail());
        self::assertSame('active', $voucher->getStatus());
        self::assertNull($voucher->getClaimedByUserId());
    }

    public function testTransferVoucherByUserWhoDoesNotOwnVoucherReturnsForbidden(): void
    {
        $client = self::createClient();
        $ownerEmail = 'transfer-voucher-real-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $otherUserEmail = 'transfer-voucher-other-user@example.com';
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            $otherUserEmail,
            'securePassword123',
        );
        $token = self::login($client, $otherUserEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Transfer Voucher Access Denied Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $ownerUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $otherUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $ownerUserId);
        $voucherId = self::createVoucherRecord(
            code: 'TRANSFER-ACCESS-DENIED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/transfer', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'recipientEmail' => 'transfer-voucher-should-not-change@example.com',
            ]),
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

        $voucher = self::getVoucherByCode('TRANSFER-ACCESS-DENIED-001');

        self::assertSame($ownerEmail, $voucher->getIssuedToEmail());
    }

    public function testTransferAlreadyClaimedVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $ownerEmail = 'transfer-voucher-claimed-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Transfer Already Claimed Voucher Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $ownerUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $ownerUserId);
        $voucherId = self::createVoucherRecord(
            code: 'TRANSFER-CLAIMED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: 'active',
            claimedByUserId: $ownerUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/transfer', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'recipientEmail' => 'transfer-voucher-claimed-recipient@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('TRANSFER-CLAIMED-001');

        self::assertSame($ownerEmail, $voucher->getIssuedToEmail());
        self::assertSame($ownerUserId, $voucher->getClaimedByUserId());
    }
}
