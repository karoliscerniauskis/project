<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class AcceptProviderInvitation
{
    public function __construct(
        private string $slug,
        private string $userId,
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
