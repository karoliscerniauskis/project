<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Command\CreateVoucher;
use App\Voucher\Application\Exception\UnableToGenerateUniqueVoucherCode;
use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use App\Voucher\Domain\Code\VoucherCodeGenerator;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;

final readonly class CreateVoucherHandler
{
    private const int ATTEMPTS_LIMIT = 5;

    public function __construct(
        private VoucherRepository $voucherRepository,
        private UuidCreator $uuidCreator,
        private Clock $clock,
        private VoucherCodeGenerator $voucherCodeGenerator,
        private VoucherTransactionManager $voucherTransactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(CreateVoucher $command): void
    {
        $issuedToUserIdRaw = $command->getIssuedToUserId();
        $issuedToUserId = $issuedToUserIdRaw !== null ? UserId::fromString($issuedToUserIdRaw) : null;

        for ($i = 0; $i < self::ATTEMPTS_LIMIT; ++$i) {
            try {
                $this->voucherTransactionManager->transactional(function () use ($command, $issuedToUserId): void {
                    $voucher = Voucher::create(
                        VoucherId::fromString($this->uuidCreator->create()),
                        $this->voucherCodeGenerator->generate(),
                        ProviderId::fromString($command->getProviderId()),
                        $issuedToUserId,
                        $command->getIssuedToEmail(),
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
