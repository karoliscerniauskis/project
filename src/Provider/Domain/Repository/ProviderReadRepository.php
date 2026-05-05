<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\View\ProvidersView;
use App\Provider\Domain\View\ProviderView;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderReadRepository
{
    public function findByUserId(UserId $userId): ProvidersView;

    public function findActiveByIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderView;

    public function findAllForAdmin(): ProvidersView;
}
