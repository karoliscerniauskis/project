<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class ValidateVoucher
{
    public function __construct(
        private string $providerId,
        private string $code,
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
}
