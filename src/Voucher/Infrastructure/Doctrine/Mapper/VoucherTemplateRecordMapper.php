<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Mapper;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\VoucherTemplateId;
use App\Voucher\Domain\Entity\VoucherTemplate;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherTemplateRecord;

final readonly class VoucherTemplateRecordMapper
{
    public function toDomain(VoucherTemplateRecord $record): VoucherTemplate
    {
        return VoucherTemplate::reconstitute(
            VoucherTemplateId::fromString($record->getId()),
            ProviderId::fromString($record->getProviderId()),
            $record->getName(),
            VoucherType::from($record->getType()),
            $record->getTitle(),
            $record->getDescription(),
            $record->getHtmlTemplate(),
            $record->getCreatedAt(),
            $record->getUpdatedAt(),
        );
    }

    public function toRecord(VoucherTemplate $voucherTemplate): VoucherTemplateRecord
    {
        return new VoucherTemplateRecord(
            $voucherTemplate->getId()->toString(),
            $voucherTemplate->getProviderId()->toString(),
            $voucherTemplate->getName(),
            $voucherTemplate->getType()->value,
            $voucherTemplate->getTitle(),
            $voucherTemplate->getDescription(),
            $voucherTemplate->getHtmlTemplate(),
            $voucherTemplate->getCreatedAt(),
            $voucherTemplate->getUpdatedAt(),
        );
    }

    public function syncRecord(VoucherTemplate $voucherTemplate, VoucherTemplateRecord $record): void
    {
        $record->setName($voucherTemplate->getName());
        $record->setType($voucherTemplate->getType()->value);
        $record->setTitle($voucherTemplate->getTitle());
        $record->setDescription($voucherTemplate->getDescription());
        $record->setHtmlTemplate($voucherTemplate->getHtmlTemplate());
        $record->setUpdatedAt($voucherTemplate->getUpdatedAt());
    }
}
