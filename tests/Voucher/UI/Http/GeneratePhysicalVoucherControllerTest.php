<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use Symfony\Component\HttpFoundation\Response;

final class GeneratePhysicalVoucherControllerTest extends ApiWebTestCase
{
    public function testGeneratePhysicalVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/generate-physical', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGeneratePhysicalVoucherWithInvalidVoucherIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'generate-physical-invalid-id@example.com',
            'securePassword123',
        );

        $client->request(
            'POST',
            '/api/vouchers/invalid-uuid/generate-physical',
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
                'message' => 'Invalid Voucher "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGeneratePhysicalVoucherForNonExistingVoucherReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'generate-physical-not-found@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/generate-physical', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGeneratePhysicalVoucherAsUserWhoCannotAccessVoucherReturnsForbidden(): void
    {
        $client = self::createClient();
        $ownerEmail = 'generate-physical-owner@example.com';
        self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $otherUserEmail = 'generate-physical-other-user@example.com';
        $token = self::registerVerifyAndLoginUser(
            $client,
            $otherUserEmail,
            'securePassword123',
        );

        $providerMemberEmail = 'generate-physical-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Generate Physical Access Denied Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'GENERATE-PHYSICAL-DENIED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/generate-physical', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGeneratePhysicalVoucherAsClaimedVoucherOwnerReturnsImageUrl(): void
    {
        $client = self::createClient();
        $ownerEmail = 'generate-physical-claimed-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'generate-physical-claimed-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Generate Physical Claimed Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'GENERATE-PHYSICAL-CLAIMED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
        );

        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/generate-physical', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('imageUrl', $response);
        self::assertIsString($response['imageUrl']);
        self::assertStringStartsWith('data:image/', $response['imageUrl']);
    }

    public function testGeneratePhysicalVoucherAsProviderMemberReturnsImageUrl(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'generate-physical-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');

        $providerId = self::createProviderRecord('Generate Physical Member Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'GENERATE-PHYSICAL-MEMBER-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'generate-physical-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/vouchers/%s/generate-physical', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('imageUrl', $response);
        self::assertIsString($response['imageUrl']);
        self::assertStringStartsWith('data:image/', $response['imageUrl']);
    }
}
