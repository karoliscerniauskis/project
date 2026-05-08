<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Console;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherReminderType;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherReminderRecord;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ProcessVoucherRemindersCommandTest extends ApiWebTestCase
{
    public function testSendsClaimReminderForActiveUnclaimedVoucherWhenProviderSettingIsConfigured(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider',
            ProviderStatus::Active->value,
            3,
            null,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-1@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'CLAIMREMINDER001',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-4 days'),
            expiresAt: new DateTimeImmutable('+10 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderExists($voucherId, VoucherReminderType::Claim);
    }

    public function testDoesNotSendClaimReminderWhenProviderSettingIsNotConfigured(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider without reminder settings',
            ProviderStatus::Active->value,
            null,
            null,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-2@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'CLAIMREMINDER002',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient2@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-10 days'),
            expiresAt: new DateTimeImmutable('+10 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Claim);
    }

    public function testDoesNotSendClaimReminderWhenVoucherIsAlreadyClaimed(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider claimed voucher',
            ProviderStatus::Active->value,
            3,
            null,
        );

        $providerAdminId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-3@example.com',
            'password123',
        );

        $claimedByUserId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-claimed-user@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $providerAdminId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'CLAIMREMINDER003',
            $providerId,
            self::getExistingProviderUser($providerId, $providerAdminId)->getId(),
            'claimed-user@example.com',
            VoucherStatus::Active->value,
            claimedByUserId: $claimedByUserId,
            createdAt: new DateTimeImmutable('-10 days'),
            expiresAt: new DateTimeImmutable('+10 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Claim);
    }

    public function testDoesNotSendClaimReminderBeforeConfiguredDaysPass(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider early reminder',
            ProviderStatus::Active->value,
            3,
            null,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-4@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'CLAIMREMINDER004',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient4@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-2 days'),
            expiresAt: new DateTimeImmutable('+10 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Claim);
    }

    public function testSendsExpiryReminderForActiveVoucherWithExpirationDateWhenProviderSettingIsConfigured(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider expiry reminder',
            ProviderStatus::Active->value,
            null,
            7,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-5@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'EXPIRYREMINDER001',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient5@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-1 day'),
            expiresAt: new DateTimeImmutable('+6 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderExists($voucherId, VoucherReminderType::Expiry);
    }

    public function testDoesNotSendExpiryReminderWhenProviderSettingIsNotConfigured(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider without expiry reminder',
            ProviderStatus::Active->value,
            null,
            null,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-6@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'EXPIRYREMINDER002',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient6@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-1 day'),
            expiresAt: new DateTimeImmutable('+6 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Expiry);
    }

    public function testDoesNotSendExpiryReminderWhenVoucherHasNoExpirationDate(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider no voucher expiry',
            ProviderStatus::Active->value,
            null,
            7,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-7@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'EXPIRYREMINDER003',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient7@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-1 day'),
            expiresAt: null,
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Expiry);
    }

    public function testDoesNotSendExpiryReminderBeforeReminderWindow(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider expiry too early',
            ProviderStatus::Active->value,
            null,
            7,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-8@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'EXPIRYREMINDER004',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient8@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-1 day'),
            expiresAt: new DateTimeImmutable('+8 days'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertVoucherReminderDoesNotExist($voucherId, VoucherReminderType::Expiry);
    }

    public function testDoesNotCreateDuplicateReminderWhenCommandIsExecutedTwice(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecordWithReminderSettings(
            'Provider duplicate reminder',
            ProviderStatus::Active->value,
            3,
            null,
        );

        $userId = self::registerVerifyAndGetUserId(
            $client,
            'voucher-reminder-provider-admin-9@example.com',
            'password123',
        );

        self::createProviderUserRecord(
            $providerId,
            $userId,
            ProviderUserRole::Admin->value,
            ProviderUserStatus::Active->value,
        );

        $voucherId = self::createVoucherRecord(
            'CLAIMREMINDER005',
            $providerId,
            self::getExistingProviderUser($providerId, $userId)->getId(),
            'recipient9@example.com',
            VoucherStatus::Active->value,
            createdAt: new DateTimeImmutable('-10 days'),
            expiresAt: new DateTimeImmutable('+10 days'),
        );

        self::executeCommand();
        self::executeCommand();

        self::assertSame(1, self::countVoucherReminders($voucherId, VoucherReminderType::Claim));
    }

    private static function assertVoucherReminderExists(string $voucherId, VoucherReminderType $type): void
    {
        self::assertSame(1, self::countVoucherReminders($voucherId, $type));
    }

    private static function assertVoucherReminderDoesNotExist(string $voucherId, VoucherReminderType $type): void
    {
        self::assertSame(0, self::countVoucherReminders($voucherId, $type));
    }

    private static function executeCommand(): CommandTester
    {
        $kernel = self::$kernel;
        self::assertInstanceOf(KernelInterface::class, $kernel);
        $application = new Application($kernel);
        $command = $application->find('app:voucher-reminders:process');
        $tester = new CommandTester($command);

        $tester->execute([]);

        return $tester;
    }

    private static function countVoucherReminders(string $voucherId, VoucherReminderType $type): int
    {
        return self::getEntityManager()
            ->getRepository(VoucherReminderRecord::class)
            ->count([
                'voucherId' => $voucherId,
                'type' => $type->value,
            ]);
    }
}
