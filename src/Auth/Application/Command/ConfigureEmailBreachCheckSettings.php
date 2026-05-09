<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class ConfigureEmailBreachCheckSettings
{
    public function __construct(
        private string $userId,
        private bool $enabled,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
