<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\View\UserProfileView;
use App\Shared\Domain\Id\UserId;

interface UserReadRepository
{
    public function getUserProfile(UserId $userId): ?UserProfileView;
}
