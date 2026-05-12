<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

final readonly class GetMyVouchers
{
    public function __construct(
        private string $userEmail,
        private string $userId,
        private ?string $codeFilter = null,
        private int $page = 1,
        private int $perPage = 20,
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

    public function getCodeFilter(): ?string
    {
        return $this->codeFilter;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
