<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Code;

use App\Voucher\Domain\Code\VoucherCodeGenerator;
use Hidehalo\Nanoid\Client;

final readonly class NanoidVoucherCodeGenerator implements VoucherCodeGenerator
{
    private const string ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const int LENGTH = 12;

    public function generate(): string
    {
        return new Client()->formattedId(self::ALPHABET, self::LENGTH);
    }
}
