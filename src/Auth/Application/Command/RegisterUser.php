<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RegisterUser
{
    public function __construct(
        private string $email,
        private string $plainPassword,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }
}
