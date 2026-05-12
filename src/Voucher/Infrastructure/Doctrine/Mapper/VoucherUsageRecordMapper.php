<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Mapper;

use App\Shared\Domain\Id\VoucherId;
use App\Shared\Domain\Id\VoucherUsageId;
use App\Voucher\Domain\Entity\VoucherUsage;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherUsageRecord;

final readonly class VoucherUsageRecordMapper
{
    public function toRecord(VoucherUsage $voucherUsage): VoucherUsageRecord
    {
        return new VoucherUsageRecord(
            $voucherUsage->getId()->toString(),
            $voucherUsage->getVoucherId()->toString(),
            $voucherUsage->getUsedAmount(),
            $voucherUsage->getUsedAt(),
        );
    }

    public function toDomain(VoucherUsageRecord $record): VoucherUsage
    {
        return VoucherUsage::reconstitute(
            VoucherUsageId::fromString($record->getId()),
            VoucherId::fromString($record->getVoucherId()),
            $record->getUsedAmount(),
            $record->getUsedAt(),
        );
    }
}
