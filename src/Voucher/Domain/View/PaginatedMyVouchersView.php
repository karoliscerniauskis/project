<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

final readonly class PaginatedMyVouchersView
{
    public function __construct(
        private MyVouchersView $vouchers,
        private int $total,
        private int $page,
        private int $perPage,
    ) {
    }

    public function getVouchers(): MyVouchersView
    {
        return $this->vouchers;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * @return array{
     *     data: list<array{
     *         id: string,
     *         code: string|null,
     *         providerId: string,
     *         providerName: string,
     *         status: string,
     *         canBeClaimed: bool,
     *         canBeTransferred: bool,
     *         canProviderBeChanged: bool,
     *         isCodeVisible: bool,
     *         type: string,
     *         initialAmount: int|null,
     *         remainingAmount: int|null,
     *         initialUsages: int|null,
     *         remainingUsages: int|null,
     *         expiresAt: string|null
     *     }>,
     *     meta: array{
     *         total: int,
     *         page: int,
     *         perPage: int,
     *         totalPages: int
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => $this->vouchers->toArray(),
            'meta' => [
                'total' => $this->total,
                'page' => $this->page,
                'perPage' => $this->perPage,
                'totalPages' => $this->getTotalPages(),
            ],
        ];
    }
}
