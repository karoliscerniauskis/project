<?php

declare(strict_types=1);

namespace App\Shared\Application\ProviderUser;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

interface ProviderUserFinder
{
    public function findIdByProviderIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderUserId;
}
