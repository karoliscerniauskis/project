<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherRepository implements VoucherRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Voucher $voucher): void
    {
        $voucherRecord = new VoucherRecord(
            $voucher->getId()->toString(),
            $voucher->getCode(),
            $voucher->getProviderId()->toString(),
            $voucher->getIssuedToUserId()?->toString(),
            $voucher->getIssuedToEmail(),
            $voucher->getClaimedByUserId()?->toString(),
        );
        $this->entityManager->persist($voucherRecord);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->entityManager->detach($voucherRecord);

            throw new VoucherCodeAlreadyExists(previous: $e);
        }
    }

    public function findByCode(string $code): ?Voucher
    {
        $voucherRecord = $this->entityManager->getRepository(VoucherRecord::class)->findOneBy(['code' => $code]);

        if (!$voucherRecord instanceof VoucherRecord) {
            return null;
        }

        $issuedToUserId = $voucherRecord->getIssuedToUserId();
        $claimedByUserId = $voucherRecord->getClaimedByUserId();

        return Voucher::reconstitute(
            VoucherId::fromString($voucherRecord->getId()),
            $voucherRecord->getCode(),
            ProviderId::fromString($voucherRecord->getProviderId()),
            $issuedToUserId !== null ? UserId::fromString($issuedToUserId) : null,
            $voucherRecord->getIssuedToEmail(),
            $claimedByUserId !== null ? UserId::fromString($claimedByUserId) : null,
        );
    }
}
