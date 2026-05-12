<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;
use App\Shared\Domain\Id\VoucherId;

final class VoucherNotFound extends AbstractApiException
{
    private function __construct(string $value, string $field)
    {
        parent::__construct(
            'Voucher was not found.',
            [self::getError($field, sprintf('Voucher "%s" was not found.', $value))],
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
