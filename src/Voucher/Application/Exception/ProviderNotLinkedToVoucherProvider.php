<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

class ProviderNotLinkedToVoucherProvider extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Validation failed.',
            [self::getError('provider', 'Provider is not linked to the voucher provider.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
