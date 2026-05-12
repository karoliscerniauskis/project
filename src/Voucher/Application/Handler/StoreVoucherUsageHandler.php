<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Domain\Id\UuidCreator;
use App\Shared\Domain\Id\VoucherUsageId;
use App\Voucher\Domain\Entity\VoucherUsage;
use App\Voucher\Domain\Event\VoucherUsed;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Domain\Repository\VoucherUsageRepository;

final readonly class StoreVoucherUsageHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherUsageRepository $voucherUsageRepository,
        private UuidCreator $uuidCreator,
    ) {
    }

    public function __invoke(VoucherUsed $event): void
    {
        $voucher = $this->voucherRepository->findByCode($event->getVoucherCode());

        if ($voucher === null) {
            return;
        }

        $voucherUsage = VoucherUsage::create(
            VoucherUsageId::fromString($this->uuidCreator->create()),
            $voucher->getId(),
            $event->getUsedAmount(),
            $event->getOccurredOn(),
        );

        $this->voucherUsageRepository->save($voucherUsage);
    }
}
