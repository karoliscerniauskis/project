<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherTemplateProviderMismatch extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Voucher template does not belong to this provider.',
            [self::getError('voucherTemplateId', 'Voucher template does not belong to this provider.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
