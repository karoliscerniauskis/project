<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Security\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class SymfonyUserPasswordHasher implements UserPasswordHasher
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function hashPassword(string $password): string
    {
        return $this->userPasswordHasher->hashPassword(new PasswordHasherUser(), $password);
    }

    public function isPasswordValid(string $password, string $hashedPassword): bool
    {
        return $this->userPasswordHasher->isPasswordValid(new PasswordHasherUser($hashedPassword), $password);
    }
}
