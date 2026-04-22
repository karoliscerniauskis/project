<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class ClaimVoucher
{
    public function __construct(
        private string $voucherId,
        private string $userId,
        private string $userEmail,
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

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
