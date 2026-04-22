<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array<array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string}>>
 */
final readonly class ProviderVouchersView implements ArrayableView
{
    /**
     * @param ProviderVoucherView[] $vouchers
     */
    public function __construct(
        private array $vouchers,
    ) {
    }

    /**
     * @return ProviderVoucherView[]
     */
    public function getVouchers(): array
    {
        return $this->vouchers;
    }

    /**
     * @return array<array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ProviderVoucherView $voucher): array => $voucher->toArray(),
            $this->vouchers,
        );
    }
}
