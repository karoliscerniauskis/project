<?php

declare(strict_types=1);

namespace App\Provider\Application\Service;

use App\Provider\Domain\Repository\ProviderLinkRepository;
use App\Shared\Application\Service\ProviderLinkChecker;
use App\Shared\Domain\Id\ProviderId;

final readonly class ProviderLinkCheckerService implements ProviderLinkChecker
{
    public function __construct(
        private ProviderLinkRepository $providerLinkRepository,
    ) {
    }

    public function areProvidersLinked(ProviderId $providerId, ProviderId $linkedProviderId): bool
    {
        $linkForward = $this->providerLinkRepository->findByProviderAndLinkedProvider($providerId, $linkedProviderId);
        $linkReverse = $this->providerLinkRepository->findByProviderAndLinkedProvider($linkedProviderId, $providerId);

        return $linkForward !== null || $linkReverse !== null;
    }
}
