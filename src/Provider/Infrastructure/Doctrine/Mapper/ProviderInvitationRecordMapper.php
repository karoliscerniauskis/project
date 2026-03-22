<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Mapper;

use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderInvitationRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;

final readonly class ProviderInvitationRecordMapper
{
    public function toDomain(ProviderInvitationRecord $record): ProviderInvitation
    {
        return ProviderInvitation::reconstitute(
            ProviderInvitationId::fromString($record->getId()),
            ProviderId::fromString($record->getProviderId()),
            $record->getEmail(),
            ProviderUserRole::from($record->getRole()),
            $record->getSlug(),
            ProviderInvitationStatus::from($record->getStatus()),
            UserId::fromString($record->getInvitedByUserId()),
            $record->getAcceptedUserId() !== null ? UserId::fromString($record->getAcceptedUserId()) : null,
            $record->getCreatedAt(),
            $record->getAcceptedAt(),
            $record->getExpiresAt(),
        );
    }

    public function toRecord(ProviderInvitation $providerInvitation): ProviderInvitationRecord
    {
        return new ProviderInvitationRecord(
            $providerInvitation->getId()->toString(),
            $providerInvitation->getProviderId()->toString(),
            $providerInvitation->getEmail(),
            $providerInvitation->getRole()->value,
            $providerInvitation->getSlug(),
            $providerInvitation->getStatus()->value,
            $providerInvitation->getInvitedByUserId()->toString(),
            $providerInvitation->getAcceptedUserId()?->toString(),
            $providerInvitation->getCreatedAt(),
            $providerInvitation->getAcceptedAt(),
            $providerInvitation->getExpiresAt(),
        );
    }
}
