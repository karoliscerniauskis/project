<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

use App\Shared\Domain\Id\UserId;

final readonly class GetProviders
{
    public function __construct(
        private UserId $userId,
        private int $page = 1,
        private int $limit = 10,
        private ?string $nameFilter = null,
    ) {
    }

    public function getUserId(): UserId
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

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getNameFilter(): ?string
    {
        return $this->nameFilter;
    }
}
