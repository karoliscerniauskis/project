<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Command\TransferVoucher;
use App\Voucher\Application\Exception\VoucherAccessDenied;
use App\Voucher\Application\Exception\VoucherAlreadyClaimed;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class TransferVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(TransferVoucher $command): void
    {
        $currentUserEmail = $command->getCurrentUserEmail();
        $voucherId = VoucherId::fromString($command->getVoucherId());
        $voucher = $this->voucherRepository->findById($voucherId);

        if (!$voucher instanceof Voucher) {
            throw VoucherNotFound::forId($voucherId);
        }

        if (!$voucher->isActive()) {
            throw VoucherNotActive::forId($voucherId);
        }

        if ($voucher->getClaimedByUserId() !== null) {
            throw VoucherAlreadyClaimed::forId($voucherId);
        }

        if ($voucher->getIssuedToEmail() !== $currentUserEmail) {
            throw VoucherAccessDenied::create();
        }

        $this->voucherTransactionManager->transactional(function () use ($voucher, $command, $currentUserEmail): void {
            $voucher->transfer($command->getRecipientEmail(), $currentUserEmail, $this->clock->now());
            $this->voucherRepository->save($voucher);
            $this->outboxWriter->storeAll($voucher->pullDomainEvents());
        });
    }
}
