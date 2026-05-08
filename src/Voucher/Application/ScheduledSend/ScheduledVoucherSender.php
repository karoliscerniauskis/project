<?php

declare(strict_types=1);

namespace App\Voucher\Application\ScheduledSend;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class ScheduledVoucherSender
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function process(): int
    {
        $now = $this->clock->now();
        $sent = 0;

        foreach ($this->voucherRepository->findScheduledSendCandidates($now) as $voucher) {
            if (!$voucher->shouldBeSent($now)) {
                continue;
            }

            $this->voucherTransactionManager->transactional(function () use ($voucher, $now, &$sent): void {
                $voucher->markAsSent($now);

                $this->voucherRepository->save($voucher);
                $this->outboxWriter->storeAll($voucher->pullDomainEvents());

                ++$sent;
            });
        }

        return $sent;
    }
}
