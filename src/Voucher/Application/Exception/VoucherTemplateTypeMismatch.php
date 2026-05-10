<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherTemplateTypeMismatch extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Voucher template type does not match voucher type.',
            [self::getError('voucherTemplateId', 'Voucher template type does not match voucher type.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
