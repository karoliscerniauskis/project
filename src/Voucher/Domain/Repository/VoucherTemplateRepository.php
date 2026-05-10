<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\VoucherTemplateId;
use App\Voucher\Domain\Entity\VoucherTemplate;

interface VoucherTemplateRepository
{
    public function save(VoucherTemplate $voucherTemplate): void;

    public function findById(VoucherTemplateId $id): ?VoucherTemplate;

    /**
     * @return list<VoucherTemplate>
     */
    public function findByProviderId(ProviderId $providerId): array;
}
