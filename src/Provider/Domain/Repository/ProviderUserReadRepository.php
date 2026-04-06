<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\View\ProviderUsersView;
use App\Shared\Domain\Id\ProviderId;

interface ProviderUserReadRepository
{
    public function findByProviderId(ProviderId $providerId): ProviderUsersView;
}
