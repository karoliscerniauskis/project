<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Query\GetMyVouchers;
use App\Voucher\Domain\Repository\VoucherReadRepository;
use App\Voucher\Domain\View\MyVouchersView;

final readonly class GetMyVouchersHandler
{
    public function __construct(
        private VoucherReadRepository $voucherReadRepository,
    ) {
    }

    public function __invoke(GetMyVouchers $query): MyVouchersView
    {
        return $this->voucherReadRepository->findByUserEmailAndUserId($query->getUserEmail(), $query->getUserId());
    }
}
