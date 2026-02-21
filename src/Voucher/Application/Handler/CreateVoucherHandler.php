<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Command\CreateVoucher;

final readonly class CreateVoucherHandler
{
    public function __invoke(CreateVoucher $command): void
    {
    }
}
