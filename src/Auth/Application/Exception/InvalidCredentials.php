<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class InvalidCredentials extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Invalid credentials.',
            [self::getError('currentPassword', 'Current password is invalid.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
