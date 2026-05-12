<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Provider\ProviderNameFinder;
use App\Shared\Application\ProviderUser\ProviderMembershipChecker;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Exception\VoucherAccessDenied;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Exception\VoucherProviderNotFound;
use App\Voucher\Application\Query\GeneratePhysicalVoucher;
use App\Voucher\Application\Service\PhysicalVoucherRenderer;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class GeneratePhysicalVoucherHandler
{
    public function __construct(
        private VoucherRepository $voucherRepository,
        private ProviderMembershipChecker $providerMembershipChecker,
        private ProviderNameFinder $providerNameFinder,
        private PhysicalVoucherRenderer $physicalVoucherRenderer,
    ) {
    }

    public function __invoke(GeneratePhysicalVoucher $command): string
    {
        $voucherId = VoucherId::fromString($command->getVoucherId());
        $userId = UserId::fromString($command->getUserId());

        $voucher = $this->voucherRepository->findById($voucherId);

        if (!$voucher instanceof Voucher) {
            throw VoucherNotFound::forId($voucherId);
        }

        $isOwner = $voucher->getClaimedByUserId() !== null
            && $voucher->getClaimedByUserId()->equals($userId);

        $isProviderMember = $this->providerMembershipChecker->isActiveMember(
            $voucher->getProviderId(),
            $userId,
        );

        if (!$isOwner && !$isProviderMember) {
            throw VoucherAccessDenied::create();
        }

        $providerName = $this->providerNameFinder->findNameById($voucher->getProviderId());

        if ($providerName === null) {
            throw VoucherProviderNotFound::forId($voucher->getProviderId());
        }

        return $this->physicalVoucherRenderer->render($voucher, $providerName);
    }
}
