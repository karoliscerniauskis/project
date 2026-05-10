<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

class ProviderAlreadyLinked extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Conflict.',
            [self::getError('provider', 'Provider is already linked.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
