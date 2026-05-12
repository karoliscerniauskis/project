<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherUsageRecord;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetVoucherUsagesControllerTest extends ApiWebTestCase
{
    public function testGetVoucherUsagesWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/usages', $voucherId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetVoucherUsagesWithInvalidVoucherIdReturnsBadRequest(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'voucher-usages-invalid-id@example.com',
            'securePassword123',
        );

        $client->request(
            'GET',
            '/api/vouchers/invalid-uuid/usages',
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

    public function testGetVoucherUsagesReturnsEmptyArrayWhenVoucherHasNoUsages(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'voucher-usages-empty@example.com',
            'securePassword123',
        );
        $voucherId = self::getUuidCreator()->create();

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/usages', $voucherId),
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

    public function testGetVoucherUsagesReturnsUsagesOrderedByUsedAtDescending(): void
    {
        $client = self::createClient();
        $providerMemberEmail = 'voucher-usages-provider-member@example.com';
        $providerMemberUserId = self::registerVerifyAndGetUserId(
            $client,
            $providerMemberEmail,
            'securePassword123',
        );
        $token = self::login($client, $providerMemberEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Voucher Usages Provider', ProviderStatus::Active->value);

        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $providerMemberUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );

        $providerUser = self::getExistingProviderUser($providerId, $providerMemberUserId);
        $voucherId = self::createVoucherRecord(
            code: 'VOUCHER-USAGES-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'voucher-usages-recipient@example.com',
            status: VoucherStatus::Active->value,
        );
        $otherVoucherId = self::createVoucherRecord(
            code: 'VOUCHER-USAGES-OTHER-001',
            providerId: $providerId,
            createdByProviderUserId: $providerUser->getId(),
            issuedToEmail: 'voucher-usages-other-recipient@example.com',
            status: VoucherStatus::Active->value,
        );

        $oldUsageId = self::createVoucherUsageRecord(
            voucherId: $voucherId,
            usedAmount: 1200,
            usedAt: new DateTimeImmutable('2020-01-01 10:00:00'),
        );
        $newUsageId = self::createVoucherUsageRecord(
            voucherId: $voucherId,
            usedAmount: null,
            usedAt: new DateTimeImmutable('2020-01-02 10:00:00'),
        );
        self::createVoucherUsageRecord(
            voucherId: $otherVoucherId,
            usedAmount: 999,
            usedAt: new DateTimeImmutable('2020-01-03 10:00:00'),
        );

        $client->request(
            'GET',
            sprintf('/api/vouchers/%s/usages', $voucherId),
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

        $newUsage = $response['data'][0];
        $oldUsage = $response['data'][1];

        self::assertIsArray($newUsage);
        self::assertIsArray($oldUsage);

        self::assertSame($newUsageId, $newUsage['id']);
        self::assertNull($newUsage['usedAmount']);
        self::assertSame('2020-01-02T10:00:00+00:00', $newUsage['usedAt']);

        self::assertSame($oldUsageId, $oldUsage['id']);
        self::assertSame(1200, $oldUsage['usedAmount']);
        self::assertSame('2020-01-01T10:00:00+00:00', $oldUsage['usedAt']);
    }

    private static function createVoucherUsageRecord(
        string $voucherId,
        ?int $usedAmount,
        DateTimeImmutable $usedAt,
    ): string {
        $usageId = self::getUuidCreator()->create();

        $usage = new VoucherUsageRecord(
            $usageId,
            $voucherId,
            $usedAmount,
            $usedAt,
        );

        $entityManager = self::getEntityManager();
        $entityManager->persist($usage);
        $entityManager->flush();

        return $usageId;
    }
}
