<?php

declare(strict_types=1);

namespace App\Tests\Voucher\UI\Console;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Event\VoucherCreated;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class SendScheduledVouchersCommandTest extends ApiWebTestCase
{
    public function testSendsScheduledVoucherWhenScheduledSendAtHasPassed(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecord(
            'Send Scheduled Voucher Provider',
            ProviderStatus::Active->value,
        );
        $userId = self::registerVerifyAndGetUserId(
            $client,
            'send-scheduled-voucher-provider-member@example.com',
            'securePassword123',
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $issuedToEmail = 'send-scheduled-voucher-recipient@example.com';
        self::createVoucherRecord(
            code: 'SCHEDULED-SEND-001',
            providerId: $providerId,
            createdByProviderUserId: self::getExistingProviderUser($providerId, $userId)->getId(),
            issuedToEmail: $issuedToEmail,
            status: VoucherStatus::Active->value,
            scheduledSendAt: new DateTimeImmutable('-1 minute'),
        );

        self::assertSame(0, self::countOutboxMessagesForEventClass(VoucherCreated::class));

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Scheduled vouchers sent: 1', $tester->getDisplay());

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertInstanceOf(DateTimeImmutable::class, $voucher->getSentAt());
        self::assertSame(1, self::countOutboxMessagesForEventClass(VoucherCreated::class));
    }

    public function testDoesNotSendScheduledVoucherBeforeScheduledSendAt(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecord(
            'Do Not Send Future Scheduled Voucher Provider',
            ProviderStatus::Active->value,
        );
        $userId = self::registerVerifyAndGetUserId(
            $client,
            'future-scheduled-voucher-provider-member@example.com',
            'securePassword123',
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $issuedToEmail = 'future-scheduled-voucher-recipient@example.com';
        self::createVoucherRecord(
            code: 'SCHEDULED-SEND-FUTURE-001',
            providerId: $providerId,
            createdByProviderUserId: self::getExistingProviderUser($providerId, $userId)->getId(),
            issuedToEmail: $issuedToEmail,
            status: VoucherStatus::Active->value,
            scheduledSendAt: new DateTimeImmutable('+1 day'),
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Scheduled vouchers sent: 0', $tester->getDisplay());

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertNull($voucher->getSentAt());
        self::assertSame(0, self::countOutboxMessagesForEventClass(VoucherCreated::class));
    }

    public function testDoesNotSendAlreadySentScheduledVoucherAgain(): void
    {
        $client = self::createClient();

        $providerId = self::createProviderRecord(
            'Do Not Resend Scheduled Voucher Provider',
            ProviderStatus::Active->value,
        );
        $userId = self::registerVerifyAndGetUserId(
            $client,
            'already-sent-scheduled-voucher-provider-member@example.com',
            'securePassword123',
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $userId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $issuedToEmail = 'already-sent-scheduled-voucher-recipient@example.com';
        $sentAt = new DateTimeImmutable('-30 minutes');

        self::createVoucherRecord(
            code: 'SCHEDULED-SEND-ALREADY-SENT-001',
            providerId: $providerId,
            createdByProviderUserId: self::getExistingProviderUser($providerId, $userId)->getId(),
            issuedToEmail: $issuedToEmail,
            status: VoucherStatus::Active->value,
            scheduledSendAt: new DateTimeImmutable('-1 hour'),
            sentAt: $sentAt,
        );

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Scheduled vouchers sent: 0', $tester->getDisplay());

        $voucher = self::getVoucherByIssuedToEmail($issuedToEmail);

        self::assertInstanceOf(DateTimeImmutable::class, $voucher->getSentAt());
        self::assertSame($sentAt->getTimestamp(), $voucher->getSentAt()->getTimestamp());
        self::assertSame(0, self::countOutboxMessagesForEventClass(VoucherCreated::class));
    }

    private static function executeCommand(): CommandTester
    {
        $kernel = self::$kernel;
        self::assertInstanceOf(KernelInterface::class, $kernel);

        $application = new Application($kernel);
        $command = $application->find('app:vouchers:send-scheduled');
        $tester = new CommandTester($command);

        $tester->execute([]);

        return $tester;
    }
}
