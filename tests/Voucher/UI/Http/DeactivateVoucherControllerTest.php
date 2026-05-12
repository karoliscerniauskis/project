<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Event\VoucherCanceled;
use Symfony\Component\HttpFoundation\Response;

final class DeactivateVoucherControllerTest extends ApiWebTestCase
{
    public function testDeactivateVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $voucherId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeactivateVoucherSuccessfullyMarksVoucherAsCanceled(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'deactivate-voucher-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Deactivate Voucher Success Provider',
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
            code: 'DEACTIVATE-SUCCESS-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'deactivate-voucher-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('DEACTIVATE-SUCCESS-001');

        self::assertSame(VoucherStatus::Canceled->value, $voucher->getStatus());
    }

    public function testDeactivateVoucherFromAnotherProviderReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'deactivate-voucher-provider-mismatch-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Deactivate Voucher Selected Provider',
            ProviderStatus::Active->value,
        );
        $otherProviderId = self::createProviderRecord(
            'Deactivate Voucher Other Provider',
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
            code: 'DEACTIVATE-PROVIDER-MISMATCH-001',
            providerId: $otherProviderId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'deactivate-voucher-provider-mismatch-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('DEACTIVATE-PROVIDER-MISMATCH-001');

        self::assertSame(VoucherStatus::Active->value, $voucher->getStatus());
    }

    public function testDeactivateAlreadyUsedVoucherReturnsConflict(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'deactivate-voucher-used-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Deactivate Used Voucher Provider',
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
            code: 'DEACTIVATE-USED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'deactivate-used-voucher-recipient@example.com',
            status: VoucherStatus::Used->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $voucher = self::getVoucherByCode('DEACTIVATE-USED-001');

        self::assertSame(VoucherStatus::Used->value, $voucher->getStatus());
    }

    public function testDeactivateNonExistingVoucherReturnsNotFound(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'deactivate-voucher-not-found-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Deactivate Non Existing Voucher Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeactivateVoucherWithInvalidProviderIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'deactivate-voucher-invalid-provider-id@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/providers/invalid-uuid/vouchers/%s/deactivate', $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Provider "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testDeactivateVoucherWithInvalidVoucherIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'deactivate-voucher-invalid-voucher-id@example.com',
            'securePassword123',
        );
        $providerId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/invalid-uuid/deactivate', $providerId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Voucher "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testDeactivateVoucherAsNonProviderMemberReturnsForbidden(): void
    {
        $client = self::createClient();
        $userEmail = 'deactivate-voucher-non-member@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $userEmail,
            'securePassword123',
        );

        $providerMemberEmail = 'deactivate-voucher-non-member-provider-user@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord(
            'Deactivate Voucher Non Member Provider',
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
            code: 'DEACTIVATE-NON-MEMBER-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'deactivate-voucher-non-member-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
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

        $voucher = self::getVoucherByCode('DEACTIVATE-NON-MEMBER-001');

        self::assertSame(VoucherStatus::Active->value, $voucher->getStatus());
    }

    public function testDeactivateVoucherSuccessfullyStoresVoucherCanceledEventInOutbox(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'deactivate-voucher-outbox-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Deactivate Voucher Outbox Provider',
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
            code: 'DEACTIVATE-OUTBOX-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'deactivate-voucher-outbox-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        self::assertSame(0, self::countOutboxMessagesForEventClass(VoucherCanceled::class));

        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers/%s/deactivate', $providerId, $voucherId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertSame(1, self::countOutboxMessagesForEventClass(VoucherCanceled::class));
    }
}
