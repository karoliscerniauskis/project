<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Code;

interface VoucherCodeGenerator
{
    public function generate(): string;
}
