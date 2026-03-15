<?php

declare(strict_types=1);

namespace App\Provider\Domain\Status;

enum ProviderStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
}
