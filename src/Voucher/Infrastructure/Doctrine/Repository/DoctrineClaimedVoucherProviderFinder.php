<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Application\Voucher\ClaimedVoucherProviderFinder;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class DoctrineClaimedVoucherProviderFinder implements ClaimedVoucherProviderFinder
{
    public function __construct(
        private VoucherRepository $voucherRepository,
    ) {
    }

    public function findProviderIdForClaimedVoucher(VoucherId $voucherId, UserId $userId): ?ProviderId
    {
        $voucher = $this->voucherRepository->findById($voucherId);
        $claimedByUserId = $voucher?->getClaimedByUserId();

        if ($voucher === null || $claimedByUserId === null || !$claimedByUserId->equals($userId)) {
            return null;
        }

        return $voucher->getProviderId();
    }
}
