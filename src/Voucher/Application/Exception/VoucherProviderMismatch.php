<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherProviderMismatch extends AbstractApiException
{
    private function __construct(string $code)
    {
        parent::__construct(
            'Voucher does not belong to this provider.',
            [self::getError('code', sprintf('Voucher "%s" does not belong to this provider.', $code))],
        );
    }

    public static function forCode(string $code): self
    {
        return new self($code);
    }
}
