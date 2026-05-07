<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Enum;

enum VoucherType: string
{
    case Amount = 'amount';
    case Usage = 'usage';
}
