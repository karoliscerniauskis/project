<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class UserNotFound extends AbstractApiException
{
    public static function forId(string $userId): self
    {
        return new self(
            'User not found.',
            [
                self::getError(
                    'userId',
                    sprintf('User "%s" was not found.', $userId),
                ),
            ],
        );
    }
}
