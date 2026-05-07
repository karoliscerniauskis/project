<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherUsedAmountExceedsRemainingAmount extends AbstractApiException
{
    private function __construct(int $usedAmount, int $remainingAmount)
    {
        parent::__construct(
            'Used amount exceeds remaining amount.',
            [
                self::getError(
                    'amount',
                    sprintf(
                        'Used amount "%s" exceeds remaining amount "%s".',
                        self::formatAmount($usedAmount),
                        self::formatAmount($remainingAmount),
                    ),
                ),
            ],
        );
    }

    public static function forAmounts(int $usedAmount, int $remainingAmount): self
    {
        return new self($usedAmount, $remainingAmount);
    }

    private static function formatAmount(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
