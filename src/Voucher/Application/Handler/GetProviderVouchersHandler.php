<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Security\ProviderAccessChecker;
use App\Voucher\Application\Exception\VoucherAccessDenied;
use App\Voucher\Application\Query\GetProviderVouchers;
use App\Voucher\Domain\Repository\VoucherReadRepository;
use App\Voucher\Domain\View\ProviderVouchersView;

final readonly class GetProviderVouchersHandler
{
    public function __construct(
        private ProviderAccessChecker $providerAccessChecker,
        private VoucherReadRepository $voucherReadRepository,
    ) {
    }

    public function __invoke(GetProviderVouchers $query): ProviderVouchersView
    {
        if (!$this->providerAccessChecker->isMember($query->getProviderId(), $query->getUserId())) {
            throw VoucherAccessDenied::create();
        }

        return $this->voucherReadRepository->findByProviderId($query->getProviderId());
    }
}
