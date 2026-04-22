<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class DeactivateVoucher
{
    public function __construct(
        private string $providerId,
        private string $code,
        private string $userId,
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

    public function getUserId(): string
    {
        return $this->userId;
    }
}
