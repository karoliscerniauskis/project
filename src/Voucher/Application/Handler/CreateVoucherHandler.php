<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Provider\ProviderStatusChecker;
use App\Shared\Application\ProviderUser\ProviderUserFinder;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Command\CreateVoucher;
use App\Voucher\Application\Exception\ProviderInactive;
use App\Voucher\Application\Exception\ProviderUserNotFound;
use App\Voucher\Application\Exception\UnableToGenerateUniqueVoucherCode;
use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Code\VoucherCodeGenerator;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class CreateVoucherHandler
{
    private const int ATTEMPTS_LIMIT = 5;

    public function __construct(
        private VoucherRepository $voucherRepository,
        private Clock $clock,
        private VoucherCodeGenerator $voucherCodeGenerator,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
        private ProviderUserFinder $providerUserFinder,
        private ProviderStatusChecker $providerStatusChecker,
    ) {
    }

    public function __invoke(CreateVoucher $command): void
    {
        $providerId = ProviderId::fromString($command->getProviderId());
        $createdByUserId = UserId::fromString($command->getCreatedByUserId());

        if (!$this->providerStatusChecker->isActive($providerId)) {
            throw ProviderInactive::create();
        }

        $providerUserId = $this->providerUserFinder->findIdByProviderIdAndUserId($providerId, $createdByUserId);

        if ($providerUserId === null) {
            throw ProviderUserNotFound::forProviderAndUserId($providerId->toString(), $createdByUserId->toString());
        }

        $type = VoucherType::from($command->getType());

        for ($i = 0; $i < self::ATTEMPTS_LIMIT; ++$i) {
            try {
                $this->voucherTransactionManager->transactional(function () use ($command, $providerId, $providerUserId, $type): void {
                    $voucher = Voucher::create(
                        VoucherId::fromString($command->getVoucherId()),
                        $this->voucherCodeGenerator->generate(),
                        $providerId,
                        $providerUserId,
                        $command->getIssuedToEmail(),
                        $type,
                        $command->getAmount(),
                        $command->getUsages(),
                        $this->clock->now(),
                    );
                    $this->voucherRepository->save($voucher);
                    $this->outboxWriter->storeAll($voucher->pullDomainEvents());
                });

                return;
            } catch (VoucherCodeAlreadyExists) {
                continue;
            }
        }

        throw UnableToGenerateUniqueVoucherCode::afterAttempts(self::ATTEMPTS_LIMIT);
    }
}
