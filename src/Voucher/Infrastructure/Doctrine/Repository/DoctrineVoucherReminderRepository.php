<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Enum\VoucherReminderType;
use App\Voucher\Domain\Repository\VoucherReminderRepository;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherReminderRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherReminderRepository implements VoucherReminderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UuidCreator $uuidCreator,
        private Clock $clock,
    ) {
    }

    public function existsForVoucherAndType(VoucherId $voucherId, VoucherReminderType $type): bool
    {
        $record = $this->entityManager->getRepository(VoucherReminderRecord::class)->findOneBy([
            'voucherId' => $voucherId->toString(),
            'type' => $type->value,
        ]);

        return $record instanceof VoucherReminderRecord;
    }

    public function markSent(VoucherId $voucherId, VoucherReminderType $type): void
    {
        $this->entityManager->persist(new VoucherReminderRecord(
            $this->uuidCreator->create(),
            $voucherId->toString(),
            $type->value,
            $this->clock->now(),
        ));
        $this->entityManager->flush();
    }
}
