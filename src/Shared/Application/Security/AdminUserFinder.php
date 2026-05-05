<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

use App\Shared\Domain\Id\UserId;

interface AdminUserFinder
{
    /**
     * @return UserId[]
     */
    public function findAdminUserIds(): array;
}
