<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class VoucherTemplateValidationFailed extends AbstractApiException
{
    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    private function __construct(array $errors)
    {
        parent::__construct('Voucher template validation failed.', $errors);
    }

    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    public static function withErrors(array $errors): self
    {
        return new self($errors);
    }
}
