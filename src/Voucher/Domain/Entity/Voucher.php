<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Voucher\Domain\Event\VoucherCreated;
use DateTimeImmutable;

final class Voucher extends AbstractAggregateRoot
{
    private string $id;
    private string $code;
    private string $providerId;
    private ?string $issuedToUserId = null;
    private ?string $issuedToEmail = null;
    private ?string $claimedByUserId = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(
        string $id,
        string $code,
        string $providerId,
        ?string $issuedToUserId,
        ?string $issuedToEmail,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->issuedToUserId = $issuedToUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->record(new VoucherCreated($occurredOn));

        return $self;
    }

    public static function reconstitute(
        string $id,
        string $code,
        string $providerId,
        ?string $issuedToUserId,
        ?string $issuedToEmail,
        ?string $claimedByUserId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->issuedToUserId = $issuedToUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->claimedByUserId = $claimedByUserId;

        return $self;
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

    public function getIssuedToUserId(): ?string
    {
        return $this->issuedToUserId;
    }

    public function getIssuedToEmail(): ?string
    {
        return $this->issuedToEmail;
    }

    public function getClaimedByUserId(): ?string
    {
        return $this->claimedByUserId;
    }
}
