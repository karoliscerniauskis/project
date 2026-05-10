<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Query\GetProviders;
use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\View\PaginatedProvidersView;

final readonly class GetProvidersHandler
{
    public function __construct(
        private ProviderReadRepository $providerReadRepository,
    ) {
    }

    public function __invoke(GetProviders $query): PaginatedProvidersView
    {
        return $this->providerReadRepository->findByUserIdPaginated(
            $query->getUserId(),
            $query->getLimit(),
            $query->getOffset(),
            $query->getNameFilter(),
        );
    }
}
