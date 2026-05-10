<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RequestPasswordReset
{
    public function __construct(
        private string $email,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
