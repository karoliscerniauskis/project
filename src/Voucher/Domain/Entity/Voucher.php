<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Event\VoucherCreated;
use DateTimeImmutable;

final class Voucher extends AbstractAggregateRoot
{
    private VoucherId $id;
    private string $code;
    private ProviderId $providerId;
    private ProviderUserId $createdByProviderUserId;
    private string $issuedToEmail;
    private ?UserId $claimedByUserId = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(
        VoucherId $id,
        string $code,
        ProviderId $providerId,
        ProviderUserId $createdByProviderUserId,
        string $issuedToEmail,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->createdByProviderUserId = $createdByProviderUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->record(new VoucherCreated($occurredOn));

        return $self;
    }

    public static function reconstitute(
        VoucherId $id,
        string $code,
        ProviderId $providerId,
        ProviderUserId $createdByProviderUserId,
        string $issuedToEmail,
        ?UserId $claimedByUserId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->createdByProviderUserId = $createdByProviderUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->claimedByUserId = $claimedByUserId;

        return $self;
    }

    public function getId(): VoucherId
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getCreatedByProviderUserId(): ProviderUserId
    {
        return $this->createdByProviderUserId;
    }

    public function getIssuedToEmail(): string
    {
        return $this->issuedToEmail;
    }

    public function getClaimedByUserId(): ?UserId
    {
        return $this->claimedByUserId;
    }
}
