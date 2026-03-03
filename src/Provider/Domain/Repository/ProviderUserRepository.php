<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderUserRepository
{
    public function save(ProviderUser $providerUser): void;

    public function findProviderIdByUserId(UserId $userId): ?ProviderId;
}
