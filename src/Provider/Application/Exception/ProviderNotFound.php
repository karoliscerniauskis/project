<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;
use App\Shared\Domain\Id\ProviderId;

final class ProviderNotFound extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Provider not found.',
            [self::getError('provider', 'Provider not found.')],
        );
    }

    public static function forId(ProviderId $providerId): self
    {
        return new self();
    }
}
