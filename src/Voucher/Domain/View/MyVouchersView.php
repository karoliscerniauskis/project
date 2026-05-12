<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<list<array{
 *     id: string,
 *     code: string|null,
 *     providerId: string,
 *     providerName: string,
 *     status: string,
 *     canBeClaimed: bool,
 *     canBeTransferred: bool,
 *     canProviderBeChanged: bool,
 *     isCodeVisible: bool,
 *     type: string,
 *     initialAmount: int|null,
 *     remainingAmount: int|null,
 *     initialUsages: int|null,
 *     remainingUsages: int|null,
 *     expiresAt: string|null
 * }>>
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
     * @return list<array{
     *     id: string,
     *     code: string|null,
     *     providerId: string,
     *     providerName: string,
     *     status: string,
     *     canBeClaimed: bool,
     *     canBeTransferred: bool,
     *     canProviderBeChanged: bool,
     *     isCodeVisible: bool,
     *     type: string,
     *     initialAmount: int|null,
     *     remainingAmount: int|null,
     *     initialUsages: int|null,
     *     remainingUsages: int|null,
     *     expiresAt: string|null
     * }>
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
