<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

interface ProviderUserRepository
{
    public function save(ProviderUser $providerUser): void;

    public function findById(ProviderUserId $providerUserId): ?ProviderUser;

    public function isMember(ProviderId $providerId, UserId $userId): bool;

    public function isAdmin(ProviderId $providerId, UserId $userId): bool;

    /**
     * @return UserId[]
     */
    public function findUserIdsByProviderIdAndRole(ProviderId $providerId, ProviderUserRole $role): array;
}
