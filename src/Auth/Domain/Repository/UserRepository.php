<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\User;

interface UserRepository
{
    public function save(User $user): void;

    public function findByEmail(string $email): ?User;
}
