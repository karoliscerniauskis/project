<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voucher')]
class VoucherRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $code;

    #[ORM\Column(type: 'uuid')]
    private string $providerId;

    #[ORM\Column(type: 'uuid')]
    private string $createdByProviderUserId;

    #[ORM\Column(type: 'string')]
    private string $issuedToEmail;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $claimedByUserId;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(type: 'string', options: ['default' => 'amount'])]
    private string $type;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $initialAmount;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $remainingAmount;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $initialUsages;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $remainingUsages;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $scheduledSendAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $sentAt;

    public function __construct(
        string $id,
        string $code,
        string $providerId,
        string $createdByProviderUserId,
        string $issuedToEmail,
        string $status,
        string $type,
        ?int $initialAmount = null,
        ?int $remainingAmount = null,
        ?int $initialUsages = null,
        ?int $remainingUsages = null,
        ?string $claimedByUserId = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $expiresAt = null,
        ?DateTimeImmutable $scheduledSendAt = null,
        ?DateTimeImmutable $sentAt = null,
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->providerId = $providerId;
        $this->createdByProviderUserId = $createdByProviderUserId;
        $this->issuedToEmail = $issuedToEmail;
        $this->status = $status;
        $this->type = $type;
        $this->initialAmount = $initialAmount;
        $this->remainingAmount = $remainingAmount;
        $this->initialUsages = $initialUsages;
        $this->remainingUsages = $remainingUsages;
        $this->claimedByUserId = $claimedByUserId;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->expiresAt = $expiresAt;
        $this->scheduledSendAt = $scheduledSendAt;
        $this->sentAt = $sentAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getCreatedByProviderUserId(): string
    {
        return $this->createdByProviderUserId;
    }

    public function getIssuedToEmail(): string
    {
        return $this->issuedToEmail;
    }

    public function getClaimedByUserId(): ?string
    {
        return $this->claimedByUserId;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getInitialAmount(): ?int
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(?int $initialAmount): self
    {
        $this->initialAmount = $initialAmount;

        return $this;
    }

    public function getRemainingAmount(): ?int
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(?int $remainingAmount): self
    {
        $this->remainingAmount = $remainingAmount;

        return $this;
    }

    public function getInitialUsages(): ?int
    {
        return $this->initialUsages;
    }

    public function setInitialUsages(?int $initialUsages): self
    {
        $this->initialUsages = $initialUsages;

        return $this;
    }

    public function getRemainingUsages(): ?int
    {
        return $this->remainingUsages;
    }

    public function setRemainingUsages(?int $remainingUsages): self
    {
        $this->remainingUsages = $remainingUsages;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function setClaimedByUserId(?string $claimedByUserId): self
    {
        $this->claimedByUserId = $claimedByUserId;

        return $this;
    }

    public function setIssuedToEmail(string $issuedToEmail): self
    {
        $this->issuedToEmail = $issuedToEmail;

        return $this;
    }

    public function getScheduledSendAt(): ?DateTimeImmutable
    {
        return $this->scheduledSendAt;
    }

    public function setScheduledSendAt(?DateTimeImmutable $scheduledSendAt): self
    {
        $this->scheduledSendAt = $scheduledSendAt;

        return $this;
    }

    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }
}
