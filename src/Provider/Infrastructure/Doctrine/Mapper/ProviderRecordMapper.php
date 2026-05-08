<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Mapper;

use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Shared\Domain\Id\ProviderId;

final readonly class ProviderRecordMapper
{
    public function toDomain(ProviderRecord $record): Provider
    {
        return Provider::reconstitute(
            ProviderId::fromString($record->getId()),
            $record->getName(),
            ProviderStatus::from($record->getStatus()),
            $record->getClaimReminderAfterDays(),
            $record->getExpiryReminderBeforeDays(),
        );
    }

    public function toRecord(Provider $provider): ProviderRecord
    {
        return new ProviderRecord(
            $provider->getId()->toString(),
            $provider->getName(),
            $provider->getStatus()->value,
            $provider->getClaimReminderAfterDays(),
            $provider->getExpiryReminderBeforeDays(),
        );
    }

    public function syncRecord(Provider $provider, ProviderRecord $record): void
    {
        $record->setStatus($provider->getStatus()->value);
        $record->setClaimReminderAfterDays($provider->getClaimReminderAfterDays());
        $record->setExpiryReminderBeforeDays($provider->getExpiryReminderBeforeDays());
    }
}
