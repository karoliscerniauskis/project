<?php

declare(strict_types=1);

namespace App\Provider\Domain\Status;

enum ProviderUserStatus: string
{
    case Active = 'active';
    case Removed = 'removed';
}
