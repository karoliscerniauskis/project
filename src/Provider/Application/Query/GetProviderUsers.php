<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class GetProviderUsers
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
