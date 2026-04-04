<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Query\GetProvider;

final readonly class GetProviderHandler
{
    public function __invoke(GetProvider $query): void
    {
    }
}
