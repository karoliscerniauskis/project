<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ChangeVoucherProviderControllerTest extends ApiWebTestCase
{
    public function testChangeVoucherProviderWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();
        $newProviderId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testChangeVoucherProviderWithInvalidVoucherIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-voucher-provider-invalid-voucher-id@example.com',
            'securePassword123',
        );
        $newProviderId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            '/api/vouchers/invalid-uuid/change-provider',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid Voucher "invalid-uuid".',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeVoucherProviderWithInvalidNewProviderIdReturnsValidationError(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-voucher-provider-invalid-new-provider-id@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => 'invalid-uuid',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'newProviderId',
                        'message' => 'Provider ID must be a valid UUID.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testChangeVoucherProviderForNonExistingVoucherReturnsNotFound(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'change-voucher-provider-not-found@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();
        $newProviderId = self::getUuidCreator()->create();

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testChangeVoucherProviderForVoucherNotOwnedByUserReturnsForbidden(): void
    {
        $client = self::createClient();
        $ownerEmail = 'change-voucher-provider-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $otherUserEmail = 'change-voucher-provider-other-user@example.com';
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            $otherUserEmail,
            'securePassword123',
        );
        $token = self::login($client, $otherUserEmail, 'securePassword123');

        $providerMemberEmail = 'change-voucher-provider-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Change Voucher Provider Source', ProviderStatus::Active->value);
        $newProviderId = self::createProviderRecord('Change Voucher Provider Target', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CHANGE-PROVIDER-NOT-OWNED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $voucher = self::getVoucherByCode('CHANGE-PROVIDER-NOT-OWNED-001');

        self::assertSame($providerId, $voucher->getProviderId());
        self::assertNotSame($otherUserId, $voucher->getClaimedByUserId());
    }

    public function testChangeVoucherProviderWhenProvidersAreNotLinkedReturnsForbidden(): void
    {
        $client = self::createClient();
        $ownerEmail = 'change-voucher-provider-not-linked-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'change-voucher-provider-not-linked-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Change Provider Not Linked Source', ProviderStatus::Active->value);
        $newProviderId = self::createProviderRecord('Change Provider Not Linked Target', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CHANGE-PROVIDER-NOT-LINKED-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $voucher = self::getVoucherByCode('CHANGE-PROVIDER-NOT-LINKED-001');

        self::assertSame($providerId, $voucher->getProviderId());
    }

    public function testChangeVoucherProviderSuccessfullyChangesProviderWhenProvidersAreLinkedForward(): void
    {
        $client = self::createClient();
        $ownerEmail = 'change-voucher-provider-success-forward-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'change-voucher-provider-success-forward-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Change Provider Forward Source', ProviderStatus::Active->value);
        $newProviderId = self::createProviderRecord('Change Provider Forward Target', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderLinkRecord($providerId, $newProviderId);

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CHANGE-PROVIDER-FORWARD-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('CHANGE-PROVIDER-FORWARD-001');

        self::assertSame($newProviderId, $voucher->getProviderId());
    }

    public function testChangeVoucherProviderSuccessfullyChangesProviderWhenProvidersAreLinkedReverse(): void
    {
        $client = self::createClient();
        $ownerEmail = 'change-voucher-provider-success-reverse-owner@example.com';
        $ownerUserId = self::registerVerifyAndGetUserId(
            $client,
            $ownerEmail,
            'securePassword123',
        );
        $token = self::login($client, $ownerEmail, 'securePassword123');

        $providerMemberEmail = 'change-voucher-provider-success-reverse-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Change Provider Reverse Source', ProviderStatus::Active->value);
        $newProviderId = self::createProviderRecord('Change Provider Reverse Target', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderLinkRecord($newProviderId, $providerId);

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'CHANGE-PROVIDER-REVERSE-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: $ownerEmail,
            status: VoucherStatus::Active->value,
            claimedByUserId: $ownerUserId,
            sentAt: new DateTimeImmutable('-1 hour'),
        );

        $client->request(
            'PATCH',
            sprintf('/api/vouchers/%s/change-provider', $voucherId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'newProviderId' => $newProviderId,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $voucher = self::getVoucherByCode('CHANGE-PROVIDER-REVERSE-001');

        self::assertSame($newProviderId, $voucher->getProviderId());
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
