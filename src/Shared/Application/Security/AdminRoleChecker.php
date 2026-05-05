<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

use App\Shared\Domain\Id\UserId;

interface AdminRoleChecker
{
    public function isAdmin(UserId $userId): bool;
}
