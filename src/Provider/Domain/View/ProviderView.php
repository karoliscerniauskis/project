<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, name: string, status: string, isAdmin: bool, claimReminderAfterDays: ?int, expiryReminderBeforeDays: ?int}>
 */
final readonly class ProviderView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $name,
        private string $status,
        private bool $isAdmin,
        private ?int $claimReminderAfterDays = null,
        private ?int $expiryReminderBeforeDays = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function getClaimReminderAfterDays(): ?int
    {
        return $this->claimReminderAfterDays;
    }

    public function getExpiryReminderBeforeDays(): ?int
    {
        return $this->expiryReminderBeforeDays;
    }

    /**
     * @return array{id: string, name: string, status: string, isAdmin: bool, claimReminderAfterDays: ?int, expiryReminderBeforeDays: ?int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'isAdmin' => $this->isAdmin(),
            'claimReminderAfterDays' => $this->getClaimReminderAfterDays(),
            'expiryReminderBeforeDays' => $this->getExpiryReminderBeforeDays(),
        ];
    }
}
