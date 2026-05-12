<?php

declare(strict_types=1);

namespace App\Shared\Application\Voucher;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;

interface ClaimedVoucherProviderFinder
{
    public function findProviderIdForClaimedVoucher(VoucherId $voucherId, UserId $userId): ?ProviderId;
}
