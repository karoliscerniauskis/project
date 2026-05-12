<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\VoucherUsage;

interface VoucherUsageRepository
{
    public function save(VoucherUsage $voucherUsage): void;

    /**
     * @return VoucherUsage[]
     */
    public function findByVoucherId(VoucherId $voucherId): array;
}
