<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherNotActive extends AbstractApiException
{
    private function __construct(string $code)
    {
        parent::__construct(
            'Voucher is not active.',
            [self::getError('code', sprintf('Voucher "%s" is not active.', $code))],
        );
    }

    public static function forCode(string $code): self
    {
        return new self($code);
    }
}
