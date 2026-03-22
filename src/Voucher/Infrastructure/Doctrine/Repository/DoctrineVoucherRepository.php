<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use App\Voucher\Infrastructure\Doctrine\Mapper\VoucherRecordMapper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherRepository implements VoucherRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VoucherRecordMapper $voucherRecordMapper,
    ) {
    }

    public function save(Voucher $voucher): void
    {
        $voucherRecord = $this->voucherRecordMapper->toRecord($voucher);
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

        return $this->voucherRecordMapper->toDomain($voucherRecord);
    }
}
