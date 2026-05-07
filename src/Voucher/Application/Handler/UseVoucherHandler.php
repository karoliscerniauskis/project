<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Voucher\Application\Command\UseVoucher;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Exception\VoucherProviderMismatch;
use App\Voucher\Application\Exception\VoucherUsedAmountExceedsRemainingAmount;
use App\Voucher\Application\Exception\VoucherUsedAmountRequired;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class UseVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(UseVoucher $command): void
    {
        $voucher = $this->voucherRepository->findByCode($command->getCode());

        if (!$voucher instanceof Voucher) {
            throw VoucherNotFound::forCode($command->getCode());
        }

        if ($voucher->getProviderId()->toString() !== $command->getProviderId()) {
            throw VoucherProviderMismatch::forCode($command->getCode());
        }

        if (!$voucher->isActive()) {
            throw VoucherNotActive::forCode($command->getCode());
        }

        if ($voucher->getType() === VoucherType::Amount) {
            $amount = $command->getAmount();
            $remainingAmount = $voucher->getRemainingAmount();

            if ($amount === null || $amount <= 0) {
                throw VoucherUsedAmountRequired::create();
            }

            if ($remainingAmount !== null && $amount > $remainingAmount) {
                throw VoucherUsedAmountExceedsRemainingAmount::forAmounts($amount, $remainingAmount);
            }
        }

        $this->voucherTransactionManager->transactional(function () use ($voucher, $command): void {
            $voucher->use($this->clock->now(), $command->getAmount());
            $this->voucherRepository->save($voucher);
            $this->outboxWriter->storeAll($voucher->pullDomainEvents());
        });
    }
}
