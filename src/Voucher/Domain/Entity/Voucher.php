<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Event\VoucherCanceled;
use App\Voucher\Domain\Event\VoucherCreated;
use App\Voucher\Domain\Event\VoucherUsed;
use DateTimeImmutable;
use LogicException;

final class Voucher extends AbstractAggregateRoot
{
    private VoucherId $id;
    private string $code;
    private ProviderId $providerId;
    private ProviderUserId $createdByProviderUserId;
    private string $issuedToEmail;
    private ?UserId $claimedByUserId = null;
    private VoucherStatus $status;

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
        $self->status = VoucherStatus::Active;
        $self->record(new VoucherCreated($providerId->toString(), $issuedToEmail, $occurredOn));

        return $self;
    }

    public static function reconstitute(
        VoucherId $id,
        string $code,
        ProviderId $providerId,
        ProviderUserId $createdByProviderUserId,
        string $issuedToEmail,
        ?UserId $claimedByUserId,
        VoucherStatus $status,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->createdByProviderUserId = $createdByProviderUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->claimedByUserId = $claimedByUserId;
        $self->status = $status;

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

    public function getStatus(): VoucherStatus
    {
        return $this->status;
    }

    public function setStatus(VoucherStatus $status): void
    {
        $this->status = $status;
    }

    public function isActive(): bool
    {
        return $this->status === VoucherStatus::Active;
    }

    public function isCanceled(): bool
    {
        return $this->status === VoucherStatus::Canceled;
    }

    public function isUsed(): bool
    {
        return $this->status === VoucherStatus::Used;
    }

    public function use(DateTimeImmutable $occurredOn): void
    {
        if (!$this->isActive()) {
            throw new LogicException('Voucher is not active.');
        }

        $this->status = VoucherStatus::Used;
        $this->record(new VoucherUsed(
            $this->code,
            $this->issuedToEmail,
            $occurredOn,
        ));
    }

    public function cancel(DateTimeImmutable $occurredOn): void
    {
        if (!$this->isActive()) {
            throw new LogicException('Voucher is not active.');
        }

        $this->status = VoucherStatus::Canceled;
        $this->record(new VoucherCanceled(
            $this->code,
            $this->issuedToEmail,
            $occurredOn,
        ));
    }
}
