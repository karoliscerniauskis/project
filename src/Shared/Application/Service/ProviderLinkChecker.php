<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\Id\ProviderId;

interface ProviderLinkChecker
{
    public function areProvidersLinked(ProviderId $providerId, ProviderId $linkedProviderId): bool;
}
