<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{data: array<int, array{id: string, name: string, status: string}>}>
 */
final readonly class AvailableProvidersView implements ArrayableView
{
    /**
     * @param LinkedProviderView[] $providers
     */
    public function __construct(
        private array $providers,
    ) {
    }

    /**
     * @return LinkedProviderView[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return array{data: array<int, array{id: string, name: string, status: string}>}
     */
    public function toArray(): array
    {
        return [
            'data' => array_values(array_map(
                static fn (LinkedProviderView $provider): array => $provider->toArray(),
                $this->providers
            )),
        ];
    }
}
