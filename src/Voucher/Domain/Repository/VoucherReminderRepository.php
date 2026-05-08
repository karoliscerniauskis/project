<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Enum\VoucherReminderType;

interface VoucherReminderRepository
{
    public function existsForVoucherAndType(VoucherId $voucherId, VoucherReminderType $type): bool;

    public function markSent(VoucherId $voucherId, VoucherReminderType $type): void;
}
