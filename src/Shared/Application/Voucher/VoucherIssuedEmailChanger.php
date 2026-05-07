<?php

declare(strict_types=1);

namespace App\Shared\Application\Voucher;

interface VoucherIssuedEmailChanger
{
    public function changeIssuedToEmail(string $currentEmail, string $newEmail): void;
}
