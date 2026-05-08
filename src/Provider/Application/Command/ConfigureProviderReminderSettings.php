<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class ConfigureProviderReminderSettings
{
    public function __construct(
        private string $providerId,
        private string $userId,
        private ?int $claimReminderAfterDays,
        private ?int $expiryReminderBeforeDays,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getUserId(): string
    {
        return $this->userId;
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
