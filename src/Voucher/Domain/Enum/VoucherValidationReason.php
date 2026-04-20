<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Enum;

enum VoucherValidationReason: string
{
    case VoucherNotFound = 'voucherNotFound';
    case VoucherProviderMismatch = 'voucherProviderMismatch';
    case VoucherNotActive = 'voucherNotActive';
}
