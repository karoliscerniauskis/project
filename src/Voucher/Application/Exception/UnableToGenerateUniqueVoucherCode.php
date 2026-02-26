<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use RuntimeException;

final class UnableToGenerateUniqueVoucherCode extends RuntimeException
{
    public static function afterAttempts(int $attempts): self
    {
        return new self(sprintf('Unable to generate a unique voucher code after %d attempts.', $attempts));
    }
}
