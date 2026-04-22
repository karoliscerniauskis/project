<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Security\ProviderAccessChecker;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Voucher\Application\Command\DeactivateVoucher;
use App\Voucher\Application\Exception\VoucherAccessDenied;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Exception\VoucherProviderMismatch;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class DeactivateVoucherHandler
{
    public function __construct(
        private ProviderAccessChecker $providerAccessChecker,
        private VoucherRepository $voucherRepository,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(DeactivateVoucher $command): void
    {
        $userId = UserId::fromString($command->getUserId());
        $providerId = ProviderId::fromString($command->getProviderId());

        if (!$this->providerAccessChecker->isMember($providerId, $userId)) {
            throw VoucherAccessDenied::create();
        }

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

        $this->voucherTransactionManager->transactional(function () use ($voucher): void {
            $voucher->cancel($this->clock->now());
            $this->voucherRepository->save($voucher);
            $this->outboxWriter->storeAll($voucher->pullDomainEvents());
        });
    }
}
