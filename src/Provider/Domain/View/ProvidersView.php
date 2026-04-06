<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array<array{id: string, name: string, status: string, isAdmin: bool}>>
 */
final readonly class ProvidersView implements ArrayableView
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

    /**
     * @return array<array{id: string, name: string, status: string, isAdmin: bool}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ProviderView $provider): array => $provider->toArray(),
            $this->providers,
        );
    }
}
