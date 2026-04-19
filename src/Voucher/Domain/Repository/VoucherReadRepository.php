<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Voucher\Domain\View\MyVouchersView;
use App\Voucher\Domain\View\ProviderVouchersView;

interface VoucherReadRepository
{
    public function findByProviderId(ProviderId $providerId): ProviderVouchersView;

    public function findByUserEmail(string $email): MyVouchersView;
}
