<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

final readonly class GetAdminProviders
{
    public function __construct(
        private string $userId,
        private int $page = 1,
        private int $limit = 10,
        private ?string $nameFilter = null,
        private ?string $statusFilter = null,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getNameFilter(): ?string
    {
        return $this->nameFilter;
    }

    public function getStatusFilter(): ?string
    {
        return $this->statusFilter;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
