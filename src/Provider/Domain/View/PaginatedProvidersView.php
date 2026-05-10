<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{data: array<array{id: string, name: string, status: string, isAdmin: bool}>, pagination: array{page: int, limit: int, total: int, totalPages: int}}>
 */
final readonly class PaginatedProvidersView implements ArrayableView
{
    /**
     * @param ProviderView[] $providers
     */
    public function __construct(
        private array $providers,
        private int $page,
        private int $limit,
        private int $total,
    ) {
    }

    /**
     * @return ProviderView[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    /**
     * @return array{data: array<array{id: string, name: string, status: string, isAdmin: bool}>, pagination: array{page: int, limit: int, total: int, totalPages: int}}
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (ProviderView $provider): array => $provider->toArray(),
                $this->providers,
            ),
            'pagination' => [
                'page' => $this->page,
                'limit' => $this->limit,
                'total' => $this->total,
                'totalPages' => $this->getTotalPages(),
            ],
        ];
    }
}
