<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

final readonly class VoucherUsagesView
{
    /**
     * @param list<VoucherUsageView> $usages
     */
    public function __construct(
        private array $usages,
    ) {
    }

    /**
     * @return list<VoucherUsageView>
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * @return list<array{
     *     id: string,
     *     usedAmount: int|null,
     *     usedAt: string
     * }>
     */
    public function toArray(): array
    {
        return array_map(
            fn (VoucherUsageView $usage): array => $usage->toArray(),
            $this->usages,
        );
    }
}
