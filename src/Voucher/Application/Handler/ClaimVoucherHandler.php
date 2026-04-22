<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Command\ClaimVoucher;
use App\Voucher\Application\Exception\VoucherAlreadyClaimed;
use App\Voucher\Application\Exception\VoucherIssuedToEmailMismatch;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class ClaimVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(ClaimVoucher $command): void
    {
        $voucherId = VoucherId::fromString($command->getVoucherId());
        $userId = UserId::fromString($command->getUserId());
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

        if ($voucher->getIssuedToEmail() !== $command->getUserEmail()) {
            throw VoucherIssuedToEmailMismatch::forId($voucherId);
        }

        $this->voucherTransactionManager->transactional(function () use ($voucher, $userId): void {
            $voucher->claim($userId, $this->clock->now());
            $this->voucherRepository->save($voucher);
            $this->outboxWriter->storeAll($voucher->pullDomainEvents());
        });
    }
}
