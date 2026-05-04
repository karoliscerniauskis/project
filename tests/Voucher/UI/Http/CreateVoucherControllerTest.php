<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CreateVoucherControllerTest extends ApiWebTestCase
{
    public function testCreateVoucherWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers', $providerId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'issuedToEmail' => 'voucher-recipient@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateVoucherWithInvalidEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $email = 'create-voucher-invalid-email-user@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Create Voucher Invalid Email Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'issuedToEmail' => 'invalid-email',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'issuedToEmail',
                        'message' => 'Email must be valid.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCreateVoucherAsNonProviderMemberReturnsForbidden(): void
    {
        $client = self::createClient();
        $email = 'create-voucher-non-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Create Voucher Non Member Provider',
            ProviderStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'issuedToEmail' => 'voucher-recipient-non-member@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(
            [
                'message' => 'Provider user not found.',
                'errors' => [
                    [
                        'field' => 'providerId',
                        'message' => sprintf(
                            'Provider "%s" user was not found for given user "%s".',
                            $providerId,
                            $userId,
                        ),
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testCreateVoucherSuccessfullyCreatesActiveVoucher(): void
    {
        $client = self::createClient();
        $email = 'create-voucher-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Create Voucher Success Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $providerUser = self::getExistingProviderUser($providerId, $userId);
        $issuedToEmail = 'created-voucher-recipient@example.com';
        $client->request(
            'POST',
            sprintf('/api/providers/%s/vouchers', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'issuedToEmail' => $issuedToEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertSame($providerId, $voucher->getProviderId());
        self::assertSame($providerUser->getId(), $voucher->getCreatedByProviderUserId());
        self::assertSame($issuedToEmail, $voucher->getIssuedToEmail());
        self::assertSame('active', $voucher->getStatus());
        self::assertNull($voucher->getClaimedByUserId());
        self::assertNotSame('', $voucher->getCode());
    }
}
