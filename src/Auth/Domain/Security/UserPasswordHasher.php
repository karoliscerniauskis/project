<?php

declare(strict_types=1);

namespace App\Auth\Domain\Security;

interface UserPasswordHasher
{
    public function hashPassword(string $password): string;

    public function isPasswordValid(string $password, string $hashedPassword): bool;
}
