<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherNotFound extends AbstractApiException
{
    private function __construct(string $code)
    {
        parent::__construct(
            'Voucher was not found.',
            [self::getError('code', sprintf('Voucher "%s" was not found.', $code))],
        );
    }

    public static function forCode(string $code): self
    {
        return new self($code);
    }
}
