<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

final readonly class RemoveProviderUser
{
    public function __construct(
        private ProviderId $providerId,
        private ProviderUserId $providerUserId,
        private UserId $requestedByUserId,
    ) {
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getProviderUserId(): ProviderUserId
    {
        return $this->providerUserId;
    }

    public function getRequestedByUserId(): UserId
    {
        return $this->requestedByUserId;
    }
}
