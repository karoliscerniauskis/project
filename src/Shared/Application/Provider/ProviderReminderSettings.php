<?php

declare(strict_types=1);

namespace App\Shared\Application\Provider;

final readonly class ProviderReminderSettings
{
    public function __construct(
        private ?int $claimReminderAfterDays,
        private ?int $expiryReminderBeforeDays,
    ) {
    }

    public function getClaimReminderAfterDays(): ?int
    {
        return $this->claimReminderAfterDays;
    }

    public function getExpiryReminderBeforeDays(): ?int
    {
        return $this->expiryReminderBeforeDays;
    }
}
