<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherTemplateNotFound extends AbstractApiException
{
    private function __construct(string $id)
    {
        parent::__construct(
            'Voucher template was not found.',
            [self::getError('voucherTemplateId', sprintf('Voucher template "%s" was not found.', $id))],
        );
    }

    public static function withId(string $id): self
    {
        return new self($id);
    }
}
