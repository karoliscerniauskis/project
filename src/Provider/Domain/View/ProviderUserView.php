<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

final readonly class ProviderUserView
{
    public function __construct(
        private string $email,
        private string $role,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
