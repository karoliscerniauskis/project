<?php

declare(strict_types=1);

namespace App\Voucher\Application\Reminder;

use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\Provider\ProviderReminderSettingsFinder;
use App\Shared\Application\User\UserIdFinder;
use App\Shared\Domain\Clock\Clock;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherReminderType;
use App\Voucher\Domain\Repository\VoucherReminderRepository;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class VoucherReminderProcessor
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private ProviderReminderSettingsFinder $providerReminderSettingsFinder,
        private VoucherReminderRepository $voucherReminderRepository,
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private EmailSender $emailSender,
        private Clock $clock,
        private string $emailFrom,
    ) {
    }

    public function process(): int
    {
        $sent = 0;

        foreach ($this->voucherRepository->findActiveReminderCandidates() as $voucher) {
            $sent += $this->processClaimReminder($voucher);
            $sent += $this->processExpiryReminder($voucher);
        }

        return $sent;
    }

    private function processClaimReminder(Voucher $voucher): int
    {
        if ($voucher->getClaimedByUserId() !== null) {
            return 0;
        }

        $settings = $this->providerReminderSettingsFinder->findByProviderId($voucher->getProviderId());

        if ($settings === null || $settings->getClaimReminderAfterDays() === null) {
            return 0;
        }

        $now = $this->clock->now();
        $remindAt = $voucher->getCreatedAt()->modify(sprintf('+%d days', $settings->getClaimReminderAfterDays()));

        if ($remindAt > $now) {
            return 0;
        }

        if ($this->voucherReminderRepository->existsForVoucherAndType($voucher->getId(), VoucherReminderType::Claim)) {
            return 0;
        }

        $this->sendReminder(
            $voucher,
            'voucher_claim_reminder',
            'Voucher waiting for you',
            'You have an unclaimed voucher waiting in the system.',
        );

        $this->voucherReminderRepository->markSent($voucher->getId(), VoucherReminderType::Claim);

        return 1;
    }

    private function processExpiryReminder(Voucher $voucher): int
    {
        $expiresAt = $voucher->getExpiresAt();

        if ($expiresAt === null) {
            return 0;
        }

        $settings = $this->providerReminderSettingsFinder->findByProviderId($voucher->getProviderId());

        if ($settings === null || $settings->getExpiryReminderBeforeDays() === null) {
            return 0;
        }

        $now = $this->clock->now();
        $remindAt = $expiresAt->modify(sprintf('-%d days', $settings->getExpiryReminderBeforeDays()));

        if ($remindAt > $now || $expiresAt <= $now) {
            return 0;
        }

        if ($this->voucherReminderRepository->existsForVoucherAndType($voucher->getId(), VoucherReminderType::Expiry)) {
            return 0;
        }

        $this->sendReminder(
            $voucher,
            'voucher_expiry_reminder',
            'Voucher expires soon',
            'Your voucher is close to its expiration date.',
        );

        $this->voucherReminderRepository->markSent($voucher->getId(), VoucherReminderType::Expiry);

        return 1;
    }

    private function sendReminder(
        Voucher $voucher,
        string $type,
        string $title,
        string $message,
    ): void {
        $userId = $this->userIdFinder->findIdByEmail($voucher->getIssuedToEmail());

        if ($userId !== null) {
            $this->notificationSender->send(
                $userId,
                $type,
                $title,
                $message,
                [
                    'voucherId' => $voucher->getId()->toString(),
                    'providerId' => $voucher->getProviderId()->toString(),
                ],
            );
        }

        $this->emailSender->send(
            $this->emailFrom,
            $voucher->getIssuedToEmail(),
            $title,
            $message,
        );
    }
}
