<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Http;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Domain\Event\VoucherCreated;
use DateTimeImmutable;
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
                'type' => VoucherType::Amount->value,
                'amount' => 1000,
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
                'type' => VoucherType::Amount->value,
                'amount' => 1000,
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
                'type' => VoucherType::Amount->value,
                'amount' => 1000,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertSame($response['id'], $voucher->getId());
        self::assertSame($providerId, $voucher->getProviderId());
        self::assertSame($providerUser->getId(), $voucher->getCreatedByProviderUserId());
        self::assertSame($issuedToEmail, $voucher->getIssuedToEmail());
        self::assertSame('active', $voucher->getStatus());
        self::assertNull($voucher->getClaimedByUserId());
        self::assertNotSame('', $voucher->getCode());
    }

    public function testCreateVoucherWithoutScheduledSendAtSendsVoucherImmediately(): void
    {
        $client = self::createClient();
        $email = 'create-voucher-immediate-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Create Voucher Immediate Send Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $issuedToEmail = 'created-voucher-immediate-recipient@example.com';

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
                'type' => VoucherType::Amount->value,
                'amount' => 1000,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertNull($voucher->getScheduledSendAt());
        self::assertInstanceOf(DateTimeImmutable::class, $voucher->getSentAt());
        self::assertSame(1, self::countOutboxMessagesForEventClass(VoucherCreated::class));
    }

    public function testCreateVoucherWithFutureScheduledSendAtDoesNotSendVoucherImmediately(): void
    {
        $client = self::createClient();
        $email = 'create-voucher-scheduled-member@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $providerId = self::createProviderRecord(
            'Create Voucher Scheduled Send Provider',
            ProviderStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $issuedToEmail = 'created-voucher-scheduled-recipient@example.com';
        $scheduledSendAt = new DateTimeImmutable('+7 days');

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
                'type' => VoucherType::Amount->value,
                'amount' => 1000,
                'scheduledSendAt' => $scheduledSendAt->format(DATE_ATOM),
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertInstanceOf(DateTimeImmutable::class, $voucher->getScheduledSendAt());
        self::assertNull($voucher->getSentAt());
        self::assertSame(0, self::countOutboxMessagesForEventClass(VoucherCreated::class));
    }
}
