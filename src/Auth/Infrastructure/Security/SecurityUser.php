<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use InvalidArgumentException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private string $userIdentifier,
        private string $id,
        private ?string $password,
        private array $roles,
    ) {
        if ($this->userIdentifier === '') {
            throw new InvalidArgumentException('User identifier must be a non-empty string.');
        }

        if ($this->id === '') {
            throw new InvalidArgumentException('User id must be a non-empty string.');
        }
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
