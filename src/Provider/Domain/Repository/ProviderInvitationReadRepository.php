<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\View\ProviderInvitationsView;
use App\Shared\Domain\Id\ProviderId;

interface ProviderInvitationReadRepository
{
    public function findPendingByProviderId(ProviderId $providerId): ProviderInvitationsView;
}
