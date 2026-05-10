<?php

declare(strict_types=1);

namespace App\Auth\Application\Query;

final readonly class GetUserProfile
{
    public function __construct(
        public string $userId,
    ) {
    }
}
