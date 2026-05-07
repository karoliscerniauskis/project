<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class UseVoucher
{
    public function __construct(
        private string $providerId,
        private string $code,
        private ?int $amount,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }
}
