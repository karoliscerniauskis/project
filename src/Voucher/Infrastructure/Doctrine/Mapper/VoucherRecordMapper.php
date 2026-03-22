<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Mapper;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;

final readonly class VoucherRecordMapper
{
    public function toDomain(VoucherRecord $record): Voucher
    {
        $issuedToUserId = $record->getIssuedToUserId();
        $claimedByUserId = $record->getClaimedByUserId();

        return Voucher::reconstitute(
            VoucherId::fromString($record->getId()),
            $record->getCode(),
            ProviderId::fromString($record->getProviderId()),
            $issuedToUserId !== null ? UserId::fromString($issuedToUserId) : null,
            $record->getIssuedToEmail(),
            $claimedByUserId !== null ? UserId::fromString($claimedByUserId) : null,
        );
    }

    public function toRecord(Voucher $voucher): VoucherRecord
    {
        return new VoucherRecord(
            $voucher->getId()->toString(),
            $voucher->getCode(),
            $voucher->getProviderId()->toString(),
            $voucher->getIssuedToUserId()?->toString(),
            $voucher->getIssuedToEmail(),
            $voucher->getClaimedByUserId()?->toString(),
        );
    }
}
