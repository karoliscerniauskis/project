<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

use DateTimeImmutable;

final readonly class CreateVoucher
{
    public function __construct(
        private string $voucherId,
        private string $providerId,
        private string $createdByUserId,
        private string $issuedToEmail,
        private string $type,
        private ?int $amount,
        private ?int $usages,
        private ?DateTimeImmutable $expiresAt,
    ) {
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getCreatedByUserId(): string
    {
        return $this->createdByUserId;
    }

    public function getIssuedToEmail(): string
    {
        return $this->issuedToEmail;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getUsages(): ?int
    {
        return $this->usages;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
