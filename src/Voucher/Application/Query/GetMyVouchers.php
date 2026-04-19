<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class GetMyVouchers
{
    public function __construct(
        private string $userEmail,
    ) {
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
