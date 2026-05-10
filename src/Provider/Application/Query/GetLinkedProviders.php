<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

final readonly class GetLinkedProviders
{
    public function __construct(
        private string $providerId,
        private string $userId,
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
