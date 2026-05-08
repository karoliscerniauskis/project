<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Mapper;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Enum\VoucherType;
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
            VoucherType::from($record->getType()),
            $record->getInitialAmount(),
            $record->getRemainingAmount(),
            $record->getInitialUsages(),
            $record->getRemainingUsages(),
            $record->getCreatedAt(),
            $record->getExpiresAt(),
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
            $voucher->getType()->value,
            $voucher->getInitialAmount(),
            $voucher->getRemainingAmount(),
            $voucher->getInitialUsages(),
            $voucher->getRemainingUsages(),
            $voucher->getClaimedByUserId()?->toString(),
            $voucher->getCreatedAt(),
            $voucher->getExpiresAt(),
        );
    }

    public function syncRecord(Voucher $voucher, VoucherRecord $record): void
    {
        $record->setStatus($voucher->getStatus()->value);
        $record->setClaimedByUserId($voucher->getClaimedByUserId()?->toString());
        $record->setIssuedToEmail($voucher->getIssuedToEmail());
        $record->setInitialAmount($voucher->getInitialAmount());
        $record->setRemainingAmount($voucher->getRemainingAmount());
        $record->setInitialUsages($voucher->getInitialUsages());
        $record->setRemainingUsages($voucher->getRemainingUsages());
        $record->setCreatedAt($voucher->getCreatedAt());
        $record->setExpiresAt($voucher->getExpiresAt());
    }
}
