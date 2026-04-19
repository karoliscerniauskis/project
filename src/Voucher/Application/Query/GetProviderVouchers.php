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
}
