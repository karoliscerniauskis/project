<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, name: string, status: string, isAdmin: bool}>
 */
final readonly class ProviderView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $name,
        private string $status,
        private bool $isAdmin,
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

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return array{id: string, name: string, status: string, isAdmin: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'isAdmin' => $this->isAdmin(),
        ];
    }
}
