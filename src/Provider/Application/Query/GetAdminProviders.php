<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

final readonly class GetAdminProviders
{
    public function __construct(
        private string $userId,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
