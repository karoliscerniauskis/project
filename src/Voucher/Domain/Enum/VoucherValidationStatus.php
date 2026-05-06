<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Enum;

enum VoucherValidationStatus: string
{
    case Valid = 'valid';
    case NotFound = 'notFound';
    case Used = 'used';
}
