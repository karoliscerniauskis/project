<?php

declare(strict_types=1);

namespace App\Provider\Application\Query;

use App\Shared\Domain\Id\ProviderId;

final readonly class GetProvider
{
    public function __construct(
        private ProviderId $providerId,
    ) {
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }
}
