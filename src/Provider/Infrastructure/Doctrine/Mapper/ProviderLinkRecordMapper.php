<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Mapper;

use App\Provider\Domain\Entity\ProviderLink;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderLinkId;

final readonly class ProviderLinkRecordMapper
{
    public function toDomain(ProviderLinkRecord $record): ProviderLink
    {
        return ProviderLink::reconstitute(
            ProviderLinkId::fromString($record->getId()),
            ProviderId::fromString($record->getProviderId()),
            ProviderId::fromString($record->getLinkedProviderId()),
            $record->getCreatedAt(),
        );
    }

    public function toRecord(ProviderLink $providerLink): ProviderLinkRecord
    {
        return new ProviderLinkRecord(
            $providerLink->getId()->toString(),
            $providerLink->getProviderId()->toString(),
            $providerLink->getLinkedProviderId()->toString(),
            $providerLink->getCreatedAt(),
        );
    }
}
