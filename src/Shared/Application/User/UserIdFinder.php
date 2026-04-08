<?php

declare(strict_types=1);

namespace App\Shared\Application\User;

use App\Shared\Domain\Id\UserId;

interface UserIdFinder
{
    public function findIdByEmail(string $email): ?UserId;
}
