<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

final readonly class GetLinkedProvidersForVoucher
{
    public function __construct(
        private string $voucherId,
        private string $userId,
    ) {
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
