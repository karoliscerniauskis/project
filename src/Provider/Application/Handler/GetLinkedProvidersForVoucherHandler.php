<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Exception\VoucherNotFound;
use App\Provider\Application\Query\GetLinkedProvidersForVoucher;
use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\View\LinkedProvidersView;
use App\Shared\Application\Voucher\ClaimedVoucherProviderFinder;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;

final readonly class GetLinkedProvidersForVoucherHandler
{
    public function __construct(
        private ClaimedVoucherProviderFinder $claimedVoucherProviderFinder,
        private ProviderReadRepository $providerReadRepository,
    ) {
    }

    public function __invoke(GetLinkedProvidersForVoucher $query): LinkedProvidersView
    {
        $voucherId = VoucherId::fromString($query->getVoucherId());
        $providerId = $this->claimedVoucherProviderFinder->findProviderIdForClaimedVoucher(
            $voucherId,
            UserId::fromString($query->getUserId()),
        );

        if ($providerId === null) {
            throw VoucherNotFound::forId($voucherId);
        }

        return $this->providerReadRepository->findLinkedProviders($providerId);
    }
}
