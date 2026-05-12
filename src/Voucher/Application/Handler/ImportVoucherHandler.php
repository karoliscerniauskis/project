<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use App\Voucher\Application\Command\ImportVoucher;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class ImportVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(ImportVoucher $command): void
    {
        $voucher = $this->voucherRepository->findByCode($command->getCode());

        if (!$voucher instanceof Voucher) {
            throw VoucherNotFound::forCode($command->getCode());
        }

        if (!$voucher->isActive()) {
            throw VoucherNotActive::forId($voucher->getId());
        }

        $userId = UserId::fromString($command->getUserId());

        $this->voucherTransactionManager->transactional(function () use ($voucher, $userId, $command): void {
            $voucher->assignTo($command->getUserEmail());
            $voucher->claim($userId, $this->clock->now());
            $this->voucherRepository->save($voucher);
            $this->outboxWriter->storeAll($voucher->pullDomainEvents());
        });
    }
}
