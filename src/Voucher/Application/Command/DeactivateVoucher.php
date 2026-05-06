<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class DeactivateVoucher
{
    public function __construct(
        private string $providerId,
        private string $voucherId,
        private string $userId,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
