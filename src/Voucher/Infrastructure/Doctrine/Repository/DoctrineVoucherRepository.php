<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Application\Voucher\VoucherIssuedEmailChanger;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\Voucher;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Repository\VoucherRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use App\Voucher\Infrastructure\Doctrine\Mapper\VoucherRecordMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherRepository implements VoucherRepository, VoucherIssuedEmailChanger
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

    public function findById(VoucherId $id): ?Voucher
    {
        $voucherRecord = $this->entityManager->getRepository(VoucherRecord::class)->find($id->toString());

        if (!$voucherRecord instanceof VoucherRecord) {
            return null;
        }

        return $this->voucherRecordMapper->toDomain($voucherRecord);
    }

    public function changeIssuedToEmail(string $currentEmail, string $newEmail): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(VoucherRecord::class, 'voucher')
            ->set('voucher.issuedToEmail', ':newEmail')
            ->andWhere('voucher.issuedToEmail = :currentEmail')
            ->setParameter('currentEmail', $currentEmail)
            ->setParameter('newEmail', $newEmail)
            ->getQuery()
            ->execute();
    }

    /**
     * @return Voucher[]
     */
    public function findActiveReminderCandidates(): array
    {
        /** @var VoucherRecord[] $records */
        $records = $this->entityManager->getRepository(VoucherRecord::class)->findBy([
            'status' => VoucherStatus::Active->value,
        ]);

        return array_map(
            fn (VoucherRecord $record): Voucher => $this->voucherRecordMapper->toDomain($record),
            $records,
        );
    }
}
