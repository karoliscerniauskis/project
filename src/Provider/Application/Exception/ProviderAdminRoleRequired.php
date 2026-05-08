<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

class ProviderAdminRoleRequired extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Forbidden.',
            [self::getError('provider', 'Provider administrator role is required.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
