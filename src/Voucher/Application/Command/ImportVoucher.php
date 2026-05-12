<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class ImportVoucher
{
    public function __construct(
        private string $code,
        private string $userId,
        private string $userEmail,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
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
