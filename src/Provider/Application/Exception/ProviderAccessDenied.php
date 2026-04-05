<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class ProviderAccessDenied extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Forbidden.',
            [self::getError('provider', 'You are not allowed to perform this action.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
