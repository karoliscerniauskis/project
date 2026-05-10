<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class UnlinkProvider
{
    public function __construct(
        private string $providerId,
        private string $linkedProviderId,
        private string $userId,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getLinkedProviderId(): string
    {
        return $this->linkedProviderId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
