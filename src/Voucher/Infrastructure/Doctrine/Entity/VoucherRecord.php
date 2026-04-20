<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Entity;

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

    public function __construct(
        string $id,
        string $code,
        string $providerId,
        string $createdByProviderUserId,
        string $issuedToEmail,
        string $status,
        ?string $claimedByUserId = null,
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->providerId = $providerId;
        $this->createdByProviderUserId = $createdByProviderUserId;
        $this->issuedToEmail = $issuedToEmail;
        $this->status = $status;
        $this->claimedByUserId = $claimedByUserId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
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
}
