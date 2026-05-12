<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Query\GetVoucherUsages;
use App\Voucher\Domain\Repository\VoucherUsageRepository;
use App\Voucher\Domain\View\VoucherUsagesView;
use App\Voucher\Domain\View\VoucherUsageView;

final readonly class GetVoucherUsagesHandler
{
    public function __construct(
        private VoucherUsageRepository $voucherUsageRepository,
    ) {
    }

    public function __invoke(GetVoucherUsages $query): VoucherUsagesView
    {
        $usages = $this->voucherUsageRepository->findByVoucherId(VoucherId::fromString($query->getVoucherId()));

        /** @var list<VoucherUsageView> $usageViews */
        $usageViews = [];

        foreach ($usages as $usage) {
            $usageViews[] = new VoucherUsageView(
                $usage->getId()->toString(),
                $usage->getUsedAmount(),
                $usage->getUsedAt(),
            );
        }

        return new VoucherUsagesView($usageViews);
    }
}
