<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class GetVoucherUsages
{
    public function __construct(
        private string $voucherId,
    ) {
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }
}
