<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Enum;

enum VoucherStatus: string
{
    case Active = 'active';
    case Canceled = 'canceled';
    case Used = 'used';
}
