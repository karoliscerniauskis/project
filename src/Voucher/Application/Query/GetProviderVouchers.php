<?php

declare(strict_types=1);

namespace App\Voucher\Application\Query;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class GetProviderVouchers
{
    public function __construct(
        private ProviderId $providerId,
        private UserId $userId,
        private ?string $codeFilter = null,
        private int $page = 1,
        private int $perPage = 20,
    ) {
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getUserId(): UserId
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
