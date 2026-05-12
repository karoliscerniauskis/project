<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\VoucherUsage;
use App\Voucher\Domain\Repository\VoucherUsageRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherUsageRecord;
use App\Voucher\Infrastructure\Doctrine\Mapper\VoucherUsageRecordMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherUsageRepository implements VoucherUsageRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VoucherUsageRecordMapper $voucherUsageRecordMapper,
    ) {
    }

    public function save(VoucherUsage $voucherUsage): void
    {
        $this->entityManager->persist($this->voucherUsageRecordMapper->toRecord($voucherUsage));
        $this->entityManager->flush();
    }

    /**
     * @return VoucherUsage[]
     */
    public function findByVoucherId(VoucherId $voucherId): array
    {
        /** @var VoucherUsageRecord[] $records */
        $records = $this->entityManager->getRepository(VoucherUsageRecord::class)
            ->findBy(
                ['voucherId' => $voucherId->toString()],
                ['usedAt' => 'DESC'],
            );

        return array_map(
            fn (VoucherUsageRecord $record): VoucherUsage => $this->voucherUsageRecordMapper->toDomain($record),
            $records,
        );
    }
}
