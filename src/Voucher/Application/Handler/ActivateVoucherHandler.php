<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Command\ActivateVoucher;

final readonly class ActivateVoucherHandler
{
    public function __invoke(ActivateVoucher $command): void
    {
    }
}
