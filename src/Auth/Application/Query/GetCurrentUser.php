<?php

declare(strict_types=1);

namespace App\Auth\Application\Query;

use App\Shared\Domain\Id\UserId;

final readonly class GetCurrentUser
{
    public function __construct(
        private UserId $userId,
    ) {
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }
}
