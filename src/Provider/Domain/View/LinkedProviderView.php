<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, name: string, status: string}>
 */
final readonly class LinkedProviderView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $name,
        private string $status,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array{id: string, name: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
        ];
    }
}
