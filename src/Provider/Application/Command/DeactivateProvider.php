<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class DeactivateProvider
{
    public function __construct(
        private string $providerId,
        public string $userId,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
