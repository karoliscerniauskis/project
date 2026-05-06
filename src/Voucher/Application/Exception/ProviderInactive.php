<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class ProviderInactive extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Provider is inactive.',
            [self::getError('provider', 'Cannot create vouchers for an inactive provider.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
