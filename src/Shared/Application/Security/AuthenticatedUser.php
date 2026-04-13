<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

interface AuthenticatedUser
{
    public function getId(): string;

    public function getUserIdentifier(): string;
}
