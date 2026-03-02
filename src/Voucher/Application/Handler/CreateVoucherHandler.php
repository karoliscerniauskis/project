<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UuidCreator;
use App\Voucher\Application\Command\CreateVoucher;
use App\Voucher\Application\Exception\UnableToGenerateUniqueVoucherCode;
use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
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
    ) {
    }

    public function __invoke(CreateVoucher $command): void
    {
        for ($i = 0; $i < self::ATTEMPTS_LIMIT; ++$i) {
            $voucher = Voucher::create(
                $this->uuidCreator->create(),
                $this->voucherCodeGenerator->generate(),
                $command->getProviderId(),
                $command->getIssuedToUserId(),
                $command->getIssuedToEmail(),
                $this->clock->now(),
            );

            try {
                $this->voucherRepository->save($voucher);

                return;
            } catch (VoucherCodeAlreadyExists) {
            }
        }

        throw UnableToGenerateUniqueVoucherCode::afterAttempts(self::ATTEMPTS_LIMIT);
    }
}
