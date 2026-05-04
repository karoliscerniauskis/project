<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class UseVoucherControllerTest extends ApiWebTestCase
{
    public function testUseVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'code' => 'USE-UNAUTHORIZED-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUseVoucherWithBlankCodeReturnsValidationError(): void
    {
        $client = self::createClient();
        $email = 'use-voucher-blank-code-user@example.com';
        self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Use Voucher Blank Code Provider',
            ProviderStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
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

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'code',
                        'message' => 'Code is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testUseVoucherSuccessfullyMarksVoucherAsUsed(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'use-voucher-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Use Voucher Success Provider',
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
            code: 'USE-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'use-voucher-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'USE-SUCCESS-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('USE-SUCCESS-001');

        self::assertSame('used', $voucher->getStatus());
    }

    public function testUseVoucherFromAnotherProviderReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'use-voucher-provider-mismatch-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Use Voucher Selected Provider',
            ProviderStatus::Active->value,
        );
        $otherProviderId = self::createProviderRecord(
            'Use Voucher Other Provider',
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
            code: 'USE-PROVIDER-MISMATCH-001',
            providerId: $otherProviderId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'use-voucher-provider-mismatch-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'USE-PROVIDER-MISMATCH-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('USE-PROVIDER-MISMATCH-001');

        self::assertSame('active', $voucher->getStatus());
    }

    public function testUseUsedVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'use-voucher-used-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Use Used Voucher Provider',
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
            code: 'USE-USED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'use-voucher-used-recipient@example.com',
            status: VoucherStatus::Used->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'USE-USED-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('USE-USED-001');

        self::assertSame(VoucherStatus::Used->value, $voucher->getStatus());
    }

    public function testUseAlreadyUsedVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'use-voucher-already-used-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Use Already Used Voucher Provider',
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
            code: 'USE-ALREADY-USED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'use-voucher-already-used-recipient@example.com',
            status: VoucherStatus::Used->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/use', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'code' => 'USE-ALREADY-USED-001',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('USE-ALREADY-USED-001');

        self::assertSame(VoucherStatus::Used->value, $voucher->getStatus());
    }
}
