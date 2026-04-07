<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Mapper;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

final readonly class ProviderUserRecordMapper
{
    public function toDomain(ProviderUserRecord $record): ProviderUser
    {
        return ProviderUser::reconstitute(
            ProviderUserId::fromString($record->getId()),
            ProviderId::fromString($record->getProviderId()),
            UserId::fromString($record->getUserId()),
            ProviderUserRole::from($record->getRole()),
            ProviderUserStatus::from($record->getStatus()),
        );
    }

    public function toRecord(ProviderUser $providerUser): ProviderUserRecord
    {
        return new ProviderUserRecord(
            $providerUser->getId()->toString(),
            $providerUser->getProviderId()->toString(),
            $providerUser->getUserId()->toString(),
            $providerUser->getRole()->value,
            $providerUser->getStatus()->value,
        );
    }
}
