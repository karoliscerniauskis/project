<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class ProviderUserNotFound extends AbstractApiException
{
    private function __construct(string $providerId, string $userId)
    {
        parent::__construct(
            'Provider user not found.',
            [
                self::getError(
                    'providerId',
                    sprintf('Provider "%s" user was not found for given user "%s".', $providerId, $userId),
                ),
            ],
        );
    }

    public static function forProviderAndUserId(string $providerId, string $userId): self
    {
        return new self($providerId, $userId);
    }
}
