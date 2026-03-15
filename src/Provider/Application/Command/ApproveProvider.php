<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class ApproveProvider
{
    public function __construct(
        private string $providerId,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }
}
