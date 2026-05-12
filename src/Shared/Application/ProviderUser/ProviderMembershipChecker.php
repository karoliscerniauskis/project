<?php

declare(strict_types=1);

namespace App\Shared\Application\ProviderUser;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderMembershipChecker
{
    public function isActiveMember(ProviderId $providerId, UserId $userId): bool;
}
