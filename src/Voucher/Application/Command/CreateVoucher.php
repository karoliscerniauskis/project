<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class CreateVoucher
{
    public function __construct(
        private string $providerId,
        private string $createdByUserId,
        private string $issuedToEmail,
    ) {
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
}
