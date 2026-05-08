<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Repository;

use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Entity\Voucher;
use DateTimeImmutable;

interface VoucherRepository
{
    public function save(Voucher $voucher): void;

    public function findByCode(string $code): ?Voucher;

    public function findById(VoucherId $id): ?Voucher;

    /**
     * @return Voucher[]
     */
    public function findActiveReminderCandidates(): array;

    /**
     * @return Voucher[]
     */
    public function findScheduledSendCandidates(DateTimeImmutable $now): array;
}
