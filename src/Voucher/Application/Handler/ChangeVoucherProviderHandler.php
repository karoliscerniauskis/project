<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Service\ProviderLinkChecker;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Command\ChangeVoucherProvider;
use App\Voucher\Application\Exception\ProviderNotLinkedToVoucherProvider;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Exception\VoucherNotOwnedByUser;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class ChangeVoucherProviderHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private ProviderLinkChecker $providerLinkChecker,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(ChangeVoucherProvider $command): void
    {
        $voucherId = VoucherId::fromString($command->getVoucherId());
        $userId = UserId::fromString($command->getUserId());
        $newProviderId = ProviderId::fromString($command->getNewProviderId());

        $voucher = $this->voucherRepository->findById($voucherId);

        if ($voucher === null) {
            throw VoucherNotFound::forId($voucherId);
        }

        if ($voucher->getClaimedByUserId() === null || !$voucher->getClaimedByUserId()->equals($userId)) {
            throw VoucherNotOwnedByUser::create();
        }

        $currentProviderId = $voucher->getProviderId();

        if (!$this->providerLinkChecker->areProvidersLinked($currentProviderId, $newProviderId)) {
            throw ProviderNotLinkedToVoucherProvider::create();
        }

        $voucher->changeProvider($newProviderId);

        $this->transactionManager->transactional(function () use ($voucher): void {
            $this->voucherRepository->save($voucher);
        });
    }
}
