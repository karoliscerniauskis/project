<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Provider;

use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Provider\ProviderStatusChecker;
use App\Shared\Domain\Id\ProviderId;

final readonly class RepositoryProviderStatusChecker implements ProviderStatusChecker
{
    public function __construct(
        private ProviderRepository $providerRepository,
    ) {
    }

    public function isActive(ProviderId $providerId): bool
    {
        $provider = $this->providerRepository->findById($providerId);

        return $provider?->isActive() ?? false;
    }
}
