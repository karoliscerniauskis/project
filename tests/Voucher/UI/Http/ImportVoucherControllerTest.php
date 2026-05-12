<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class ImportVoucherControllerTest extends ApiWebTestCase
{
    public function testImportVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'code' => 'IMPORT-UNAUTHORIZED-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testImportVoucherWithInvalidJsonBodyReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'import-voucher-invalid-json@example.com',
            'securePassword123',
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            '{invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid request body',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testImportVoucherWithoutCodeReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'import-voucher-missing-code@example.com',
            'securePassword123',
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Code is required',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testImportVoucherWithEmptyCodeReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'import-voucher-empty-code@example.com',
            'securePassword123',
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => '',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Code is required',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testImportNonExistingVoucherReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'import-voucher-not-found@example.com',
            'securePassword123',
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'IMPORT-NOT-FOUND-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertSame(
            [
                'message' => 'Voucher was not found.',
                'errors' => [
                    [
                        'field' => 'code',
                        'message' => 'Voucher "IMPORT-NOT-FOUND-001" was not found.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testImportInactiveVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $userEmail = 'import-voucher-inactive-user@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $userEmail,
            'securePassword123',
        );

        $providerMemberEmail = 'import-voucher-inactive-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Import Inactive Voucher Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        self::createVoucherRecord(
            code: 'IMPORT-INACTIVE-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'original-import-inactive-recipient@example.com',
            status: VoucherStatus::Canceled->value,
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'IMPORT-INACTIVE-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('IMPORT-INACTIVE-001');

        self::assertSame('original-import-inactive-recipient@example.com', $voucher->getIssuedToEmail());
        self::assertNull($voucher->getClaimedByUserId());
    }

    public function testImportVoucherSuccessfullyAssignsAndClaimsVoucher(): void
    {
        $client = self::createClient();
        $userEmail = 'import-voucher-success-user@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $userEmail,
            'securePassword123',
        );
        $token = self::login($client, $userEmail, 'securePassword123');

        $providerMemberEmail = 'import-voucher-success-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Import Voucher Success Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        self::createVoucherRecord(
            code: 'IMPORT-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'original-import-success-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            '/api/vouchers/import',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'IMPORT-SUCCESS-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('IMPORT-SUCCESS-001');

        self::assertSame($userEmail, $voucher->getIssuedToEmail());
        self::assertSame($userId, $voucher->getClaimedByUserId());
    }
}
