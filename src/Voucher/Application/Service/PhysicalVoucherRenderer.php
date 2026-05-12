<?php

declare(strict_types=1);

namespace App\Voucher\Application\Service;

use App\Voucher\Domain\Entity\Voucher;

interface PhysicalVoucherRenderer
{
    public function render(Voucher $voucher, string $providerName): string;
}
