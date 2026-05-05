<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use RuntimeException;

final class AdminRoleRequired extends RuntimeException
{
    public static function create(): self
    {
        return new self('Administrator role is required.');
    }
}
