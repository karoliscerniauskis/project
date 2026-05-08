<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'provider')]
class ProviderRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $claimReminderAfterDays;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $expiryReminderBeforeDays;

    public function __construct(
        string $id,
        string $name,
        string $status,
        ?int $claimReminderAfterDays = null,
        ?int $expiryReminderBeforeDays = null,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->claimReminderAfterDays = $claimReminderAfterDays;
        $this->expiryReminderBeforeDays = $expiryReminderBeforeDays;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClaimReminderAfterDays(): ?int
    {
        return $this->claimReminderAfterDays;
    }

    public function setClaimReminderAfterDays(?int $claimReminderAfterDays): self
    {
        $this->claimReminderAfterDays = $claimReminderAfterDays;

        return $this;
    }

    public function getExpiryReminderBeforeDays(): ?int
    {
        return $this->expiryReminderBeforeDays;
    }

    public function setExpiryReminderBeforeDays(?int $expiryReminderBeforeDays): self
    {
        $this->expiryReminderBeforeDays = $expiryReminderBeforeDays;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
