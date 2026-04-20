<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use App\Voucher\Infrastructure\Doctrine\Mapper\VoucherRecordMapper;
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
        $existing = $this->entityManager->getRepository(VoucherRecord::class)->find($voucher->getId()->toString());

        if ($existing instanceof VoucherRecord) {
            $this->voucherRecordMapper->syncRecord($voucher, $existing);

            return;
        }

        $this->entityManager->persist($this->voucherRecordMapper->toRecord($voucher));
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
