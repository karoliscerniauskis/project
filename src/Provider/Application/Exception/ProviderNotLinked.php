<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

class ProviderNotLinked extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Not found.',
            [self::getError('provider', 'Provider link not found.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
