<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class CreateVoucher
{
    public function __construct(
        private string $providerId,
        private ?string $issuedToUserId = null,
        private ?string $issuedToEmail = null,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getIssuedToUserId(): ?string
    {
        return $this->issuedToUserId;
    }

    public function getIssuedToEmail(): ?string
    {
        return $this->issuedToEmail;
    }
}
