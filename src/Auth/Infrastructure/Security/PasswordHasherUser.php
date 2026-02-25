<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final readonly class PasswordHasherUser implements PasswordAuthenticatedUserInterface
{
    public function __construct(private ?string $password = null)
    {
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
