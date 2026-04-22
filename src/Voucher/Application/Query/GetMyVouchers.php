<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class GetMyVouchers
{
    public function __construct(
        private string $userEmail,
        private string $userId,
    ) {
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
