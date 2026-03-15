<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class CreateProvider
{
    public function __construct(
        private string $userId,
        private string $name,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
