<?php

declare(strict_types=1);

namespace App\Provider\Domain\Role;

enum ProviderUserRole: string
{
    case Admin = 'admin';
    case Member = 'member';
}
