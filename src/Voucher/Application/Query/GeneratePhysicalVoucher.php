<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class GeneratePhysicalVoucher
{
    public function __construct(
        private string $voucherId,
        private string $userId,
    ) {
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
