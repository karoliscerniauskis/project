<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array<array{id: string, code: string|null, providerName: string}>>
 */
final readonly class MyVouchersView implements ArrayableView
{
    /**
     * @param MyVoucherView[] $vouchers
     */
    public function __construct(
        private array $vouchers,
    ) {
    }

    /**
     * @return MyVoucherView[]
     */
    public function getVouchers(): array
    {
        return $this->vouchers;
    }

    /**
     * @return array<array{id: string, code: string|null, providerName: string}>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->vouchers as $voucher) {
            $result[] = $voucher->toArray();
        }

        return $result;
    }
}
