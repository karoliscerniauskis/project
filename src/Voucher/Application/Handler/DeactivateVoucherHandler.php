<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Command\DeactivateVoucher;

final readonly class DeactivateVoucherHandler
{
    public function __invoke(DeactivateVoucher $command): void
    {
    }
}
