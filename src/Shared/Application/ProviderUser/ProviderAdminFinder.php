<?php

declare(strict_types=1);

namespace App\Shared\Application\ProviderUser;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderAdminFinder
{
    /**
     * @return UserId[]
     */
    public function findAdminUserIdsByProviderId(ProviderId $providerId): array;
}
