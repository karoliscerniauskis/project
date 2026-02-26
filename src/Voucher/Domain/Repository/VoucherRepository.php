<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Voucher\Domain\Entity\Voucher;

interface VoucherRepository
{
    public function save(Voucher $voucher): void;

    public function findByCode(string $code): ?Voucher;
}
