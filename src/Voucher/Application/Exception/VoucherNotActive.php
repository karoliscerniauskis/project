<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;
use App\Shared\Domain\Id\VoucherId;

final class VoucherNotActive extends AbstractApiException
{
    private function __construct(string $value, string $field)
    {
        parent::__construct(
            'Voucher is not active.',
            [self::getError($field, sprintf('Voucher "%s" is not active.', $value))],
        );
    }

    public static function forCode(string $code): self
    {
        return new self($code, 'code');
    }

    public static function forId(VoucherId $id): self
    {
        return new self($id->toString(), 'id');
    }
}
