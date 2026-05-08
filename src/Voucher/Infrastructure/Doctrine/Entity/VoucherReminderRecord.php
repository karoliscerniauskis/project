<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voucher_reminder')]
#[ORM\UniqueConstraint(name: 'uniq_voucher_reminder_type', columns: ['voucher_id', 'type'])]
class VoucherReminderRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'uuid')]
    private string $voucherId;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $sentAt;

    public function __construct(
        string $id,
        string $voucherId,
        string $type,
        DateTimeImmutable $sentAt,
    ) {
        $this->id = $id;
        $this->voucherId = $voucherId;
        $this->type = $type;
        $this->sentAt = $sentAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSentAt(): DateTimeImmutable
    {
        return $this->sentAt;
    }
}
