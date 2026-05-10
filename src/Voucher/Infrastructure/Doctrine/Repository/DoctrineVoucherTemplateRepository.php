<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\VoucherTemplateId;
use App\Voucher\Domain\Entity\VoucherTemplate;
use App\Voucher\Domain\Repository\VoucherTemplateRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherTemplateRecord;
use App\Voucher\Infrastructure\Doctrine\Mapper\VoucherTemplateRecordMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherTemplateRepository implements VoucherTemplateRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VoucherTemplateRecordMapper $mapper,
    ) {
    }

    public function save(VoucherTemplate $voucherTemplate): void
    {
        $record = $this->entityManager->find(
            VoucherTemplateRecord::class,
            $voucherTemplate->getId()->toString(),
        );

        if (!$record instanceof VoucherTemplateRecord) {
            $this->entityManager->persist($this->mapper->toRecord($voucherTemplate));
            $this->entityManager->flush();

            return;
        }

        $this->mapper->syncRecord($voucherTemplate, $record);
        $this->entityManager->flush();
    }

    public function findById(VoucherTemplateId $id): ?VoucherTemplate
    {
        $record = $this->entityManager->find(VoucherTemplateRecord::class, $id->toString());

        if (!$record instanceof VoucherTemplateRecord) {
            return null;
        }

        return $this->mapper->toDomain($record);
    }

    public function findByProviderId(ProviderId $providerId): array
    {
        $records = $this->entityManager
            ->getRepository(VoucherTemplateRecord::class)
            ->findBy(['providerId' => $providerId->toString()], ['createdAt' => 'DESC']);

        return array_map(
            fn (VoucherTemplateRecord $record): VoucherTemplate => $this->mapper->toDomain($record),
            $records,
        );
    }
}
