<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderAccessChecker
{
    public function isMember(ProviderId $providerId, UserId $userId): bool;

    public function isAdmin(ProviderId $providerId, UserId $userId): bool;
}
