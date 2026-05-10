<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\View\AvailableProvidersView;
use App\Provider\Domain\View\LinkedProvidersView;
use App\Provider\Domain\View\PaginatedProvidersView;
use App\Provider\Domain\View\ProvidersView;
use App\Provider\Domain\View\ProviderView;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

interface ProviderReadRepository
{
    public function findByUserId(UserId $userId): ProvidersView;

    public function findByUserIdPaginated(
        UserId $userId,
        int $limit,
        int $offset,
        ?string $nameFilter = null,
    ): PaginatedProvidersView;

    public function findActiveByIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderView;

    public function findAllForAdmin(): ProvidersView;

    public function findAllForAdminPaginated(
        int $limit,
        int $offset,
        ?string $nameFilter = null,
        ?string $statusFilter = null,
    ): PaginatedProvidersView;

    public function findLinkedProviders(ProviderId $providerId): LinkedProvidersView;

    public function findAvailableProvidersToLink(ProviderId $providerId, UserId $userId): AvailableProvidersView;
}
