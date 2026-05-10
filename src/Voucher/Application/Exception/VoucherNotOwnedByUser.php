<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

class VoucherNotOwnedByUser extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Access denied.',
            [self::getError('voucher', 'Voucher is not owned by the user.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
