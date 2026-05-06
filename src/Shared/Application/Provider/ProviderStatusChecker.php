<?php

declare(strict_types=1);

namespace App\Shared\Application\Provider;

use App\Shared\Domain\Id\ProviderId;

interface ProviderStatusChecker
{
    public function isActive(ProviderId $providerId): bool;
}
