<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Voucher\Domain\View\MyVouchersView;
use App\Voucher\Domain\View\ProviderVouchersView;

interface VoucherReadRepository
{
    public function findByProviderId(
        ProviderId $providerId,
        ?string $codeFilter = null,
        int $limit = 20,
        int $offset = 0,
    ): ProviderVouchersView;

    public function findByUserEmailAndUserId(
        string $email,
        string $userId,
        ?string $codeFilter = null,
        int $limit = 20,
        int $offset = 0,
    ): MyVouchersView;

    public function countByProviderId(ProviderId $providerId, ?string $codeFilter = null): int;

    public function countByUserEmailAndUserId(string $email, string $userId, ?string $codeFilter = null): int;
}
