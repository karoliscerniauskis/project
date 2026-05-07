<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherUsedAmountRequired extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Used amount is required.',
            [self::getError('amount', 'Used amount is required for amount vouchers.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
