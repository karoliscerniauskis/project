<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Query\GetProvider;
use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\View\ProviderView;

final readonly class GetProviderHandler
{
    public function __construct(
        private ProviderReadRepository $providerReadRepository,
    ) {
    }

    public function __invoke(GetProvider $query): ?ProviderView
    {
        return $this->providerReadRepository->findByIdAndUserId(
            $query->getProviderId(),
            $query->getUserId(),
        );
    }
}
