<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Id\VoucherId;
use App\Shared\Domain\Id\VoucherUsageId;
use DateTimeImmutable;

final class VoucherUsage
{
    private VoucherUsageId $id;
    private VoucherId $voucherId;
    private ?int $usedAmount;
    private DateTimeImmutable $usedAt;

    private function __construct()
    {
    }

    public static function create(
        VoucherUsageId $id,
        VoucherId $voucherId,
        ?int $usedAmount,
        DateTimeImmutable $usedAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->voucherId = $voucherId;
        $self->usedAmount = $usedAmount;
        $self->usedAt = $usedAt;

        return $self;
    }

    public static function reconstitute(
        VoucherUsageId $id,
        VoucherId $voucherId,
        ?int $usedAmount,
        DateTimeImmutable $usedAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->voucherId = $voucherId;
        $self->usedAmount = $usedAmount;
        $self->usedAt = $usedAt;

        return $self;
    }

    public function getId(): VoucherUsageId
    {
        return $this->id;
    }

    public function getVoucherId(): VoucherId
    {
        return $this->voucherId;
    }

    public function getUsedAmount(): ?int
    {
        return $this->usedAmount;
    }

    public function getUsedAt(): DateTimeImmutable
    {
        return $this->usedAt;
    }
}
