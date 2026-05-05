<?php

declare(strict_types=1);

namespace App\Voucher\Application\Url;

interface FrontendUrlCreator
{
    public function myVouchers(): string;

    public function providerVouchers(string $providerId): string;
}
