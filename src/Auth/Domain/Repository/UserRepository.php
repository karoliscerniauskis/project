<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\User;
use App\Shared\Domain\Id\UserId;

interface UserRepository
{
    public function save(User $user): void;

    public function findByEmail(string $email): ?User;

    public function findByEmailVerificationSlug(string $emailVerificationSlug): ?User;

    public function findById(UserId $id): ?User;
}
