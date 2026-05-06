<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Voucher\Application\Query\ValidateVoucher;
use App\Voucher\Domain\Enum\VoucherValidationReason;
use App\Voucher\Domain\Enum\VoucherValidationStatus;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Domain\View\VoucherValidationView;

final readonly class ValidateVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
    ) {
    }

    public function __invoke(ValidateVoucher $query): VoucherValidationView
    {
        $voucher = $this->voucherRepository->findByCode($query->getCode());

        if ($voucher === null || $voucher->getProviderId()->toString() !== $query->getProviderId()) {
            return new VoucherValidationView(
                false,
                VoucherValidationStatus::NotFound,
                VoucherValidationReason::VoucherNotFound,
            );
        }

        if (!$voucher->isActive()) {
            return new VoucherValidationView(
                false,
                VoucherValidationStatus::Used,
                VoucherValidationReason::VoucherNotActive,
            );
        }

        return new VoucherValidationView(true, VoucherValidationStatus::Valid);
    }
}
