<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\View\ProvidersView;
use App\Shared\Domain\Id\UserId;

interface ProviderReadRepository
{
    public function findByUserId(UserId $userId): ProvidersView;
}
