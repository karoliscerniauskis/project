<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

final readonly class ProvidersView
{
    /**
     * @param ProviderView[] $providers
     */
    public function __construct(
        private array $providers,
    ) {
    }

    /**
     * @return ProviderView[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
