<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Query\GetMyVouchers;
use App\Voucher\Domain\Repository\VoucherReadRepository;
use App\Voucher\Domain\View\PaginatedMyVouchersView;

final readonly class GetMyVouchersHandler
{
    public function __construct(
        private VoucherReadRepository $voucherReadRepository,
    ) {
    }

    public function __invoke(GetMyVouchers $query): PaginatedMyVouchersView
    {
        $vouchers = $this->voucherReadRepository->findByUserEmailAndUserId(
            $query->getUserEmail(),
            $query->getUserId(),
            $query->getCodeFilter(),
            $query->getPerPage(),
            $query->getOffset()
        );

        $total = $this->voucherReadRepository->countByUserEmailAndUserId(
            $query->getUserEmail(),
            $query->getUserId(),
            $query->getCodeFilter()
        );

        return new PaginatedMyVouchersView(
            $vouchers,
            $total,
            $query->getPage(),
            $query->getPerPage()
        );
    }
}
