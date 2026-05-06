<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;
use App\Shared\Domain\Id\VoucherId;

final class VoucherProviderMismatch extends AbstractApiException
{
    private function __construct(string $value, string $field)
    {
        parent::__construct(
            'Voucher does not belong to this provider.',
            [self::getError($field, sprintf('Voucher "%s" does not belong to this provider.', $value))],
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
