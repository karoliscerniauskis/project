<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Mapper;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;

final readonly class VoucherRecordMapper
{
    public function toDomain(VoucherRecord $record): Voucher
    {
        $claimedByUserId = $record->getClaimedByUserId();

        return Voucher::reconstitute(
            VoucherId::fromString($record->getId()),
            $record->getCode(),
            ProviderId::fromString($record->getProviderId()),
            ProviderUserId::fromString($record->getCreatedByProviderUserId()),
            $record->getIssuedToEmail(),
            $claimedByUserId !== null ? UserId::fromString($claimedByUserId) : null,
            VoucherStatus::from($record->getStatus()),
        );
    }

    public function toRecord(Voucher $voucher): VoucherRecord
    {
        return new VoucherRecord(
            $voucher->getId()->toString(),
            $voucher->getCode(),
            $voucher->getProviderId()->toString(),
            $voucher->getCreatedByProviderUserId()->toString(),
            $voucher->getIssuedToEmail(),
            $voucher->getStatus()->value,
            $voucher->getClaimedByUserId()?->toString(),
        );
    }

    public function syncRecord(Voucher $voucher, VoucherRecord $record): void
    {
        $record->setStatus($voucher->getStatus()->value);
    }
}
