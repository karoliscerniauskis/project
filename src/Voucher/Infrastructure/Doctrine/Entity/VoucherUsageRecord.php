<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voucher_usage')]
#[ORM\Index(columns: ['voucher_id'], name: 'idx_voucher_usage_voucher_id')]
#[ORM\Index(columns: ['used_at'], name: 'idx_voucher_usage_used_at')]
class VoucherUsageRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'uuid')]
    private string $voucherId;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $usedAmount;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $usedAt;

    public function __construct(
        string $id,
        string $voucherId,
        ?int $usedAmount,
        DateTimeImmutable $usedAt,
    ) {
        $this->id = $id;
        $this->voucherId = $voucherId;
        $this->usedAmount = $usedAmount;
        $this->usedAt = $usedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVoucherId(): string
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
