<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

use App\Shared\Domain\Id\UserId;

final readonly class GetProviders
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
