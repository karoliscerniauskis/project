<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

final readonly class ProviderView
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
}
