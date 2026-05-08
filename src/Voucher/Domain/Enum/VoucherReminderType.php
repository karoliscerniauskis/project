<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Enum;

enum VoucherReminderType: string
{
    case Claim = 'claim';
    case Expiry = 'expiry';
}
