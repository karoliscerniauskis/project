<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherId;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Domain\Event\VoucherCanceled;
use App\Voucher\Domain\Event\VoucherClaimed;
use App\Voucher\Domain\Event\VoucherCreated;
use App\Voucher\Domain\Event\VoucherTransferred;
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
    private VoucherType $type;
    private ?int $initialAmount;
    private ?int $remainingAmount;
    private ?int $initialUsages;
    private ?int $remainingUsages;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $expiresAt;

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
        VoucherType $type,
        ?int $initialAmount,
        ?int $initialUsages,
        ?DateTimeImmutable $expiresAt,
        DateTimeImmutable $occurredOn,
    ): self {
        if ($type === VoucherType::Amount && ($initialAmount === null || $initialAmount <= 0)) {
            throw new LogicException('Amount voucher must have a positive amount.');
        }

        if ($type === VoucherType::Usage && ($initialUsages === null || $initialUsages <= 0)) {
            throw new LogicException('Usage voucher must have a positive usage count.');
        }

        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->createdByProviderUserId = $createdByProviderUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->status = VoucherStatus::Active;
        $self->type = $type;
        $self->initialAmount = $type === VoucherType::Amount ? $initialAmount : null;
        $self->remainingAmount = $type === VoucherType::Amount ? $initialAmount : null;
        $self->initialUsages = $type === VoucherType::Usage ? $initialUsages : null;
        $self->remainingUsages = $type === VoucherType::Usage ? $initialUsages : null;
        $self->createdAt = $occurredOn;
        $self->expiresAt = $expiresAt;
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
        VoucherType $type,
        ?int $initialAmount,
        ?int $remainingAmount,
        ?int $initialUsages,
        ?int $remainingUsages,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $expiresAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->providerId = $providerId;
        $self->createdByProviderUserId = $createdByProviderUserId;
        $self->issuedToEmail = $issuedToEmail;
        $self->claimedByUserId = $claimedByUserId;
        $self->status = $status;
        $self->type = $type;
        $self->initialAmount = $initialAmount;
        $self->remainingAmount = $remainingAmount;
        $self->initialUsages = $initialUsages;
        $self->remainingUsages = $remainingUsages;
        $self->createdAt = $createdAt;
        $self->expiresAt = $expiresAt;

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

    public function getType(): VoucherType
    {
        return $this->type;
    }

    public function getInitialAmount(): ?int
    {
        return $this->initialAmount;
    }

    public function getRemainingAmount(): ?int
    {
        return $this->remainingAmount;
    }

    public function getInitialUsages(): ?int
    {
        return $this->initialUsages;
    }

    public function getRemainingUsages(): ?int
    {
        return $this->remainingUsages;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(DateTimeImmutable $now): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= $now;
    }

    public function use(DateTimeImmutable $occurredOn, ?int $amount = null): void
    {
        if (!$this->isActive()) {
            throw new LogicException('Voucher is not active.');
        }

        if ($this->isExpired($occurredOn)) {
            throw new LogicException('Voucher is expired.');
        }

        if ($this->type === VoucherType::Usage) {
            if ($this->remainingUsages === null || $this->remainingUsages <= 0) {
                throw new LogicException('Voucher has no remaining usages.');
            }

            --$this->remainingUsages;

            if ($this->remainingUsages === 0) {
                $this->status = VoucherStatus::Used;
            }

            $this->record(new VoucherUsed(
                $this->code,
                $this->issuedToEmail,
                $occurredOn,
            ));

            return;
        }

        if ($this->remainingAmount === null || $this->remainingAmount <= 0) {
            throw new LogicException('Voucher has no remaining amount.');
        }

        if ($amount === null || $amount <= 0) {
            throw new LogicException('Used amount must be positive.');
        }

        if ($amount > $this->remainingAmount) {
            throw new LogicException('Used amount cannot be greater than remaining amount.');
        }

        $this->remainingAmount -= $amount;

        if ($this->remainingAmount === 0) {
            $this->status = VoucherStatus::Used;
        }

        $this->record(new VoucherUsed(
            $this->code,
            $this->issuedToEmail,
            $occurredOn,
        ));
    }

    public function claim(UserId $claimedByUserId, DateTimeImmutable $occurredOn): void
    {
        if (!$this->isActive()) {
            throw new LogicException('Voucher is not active.');
        }

        if ($this->isExpired($occurredOn)) {
            throw new LogicException('Voucher is expired.');
        }

        if ($this->claimedByUserId !== null) {
            throw new LogicException('Voucher is already claimed.');
        }

        $this->claimedByUserId = $claimedByUserId;
        $this->record(new VoucherClaimed(
            $this->providerId->toString(),
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

    public function transfer(
        string $issuedToEmail,
        string $transferredFromEmail,
        string $newCode,
        DateTimeImmutable $occurredOn,
    ): void {
        if (!$this->isActive()) {
            throw new LogicException('Voucher is not active.');
        }

        if ($this->claimedByUserId !== null) {
            throw new LogicException('Voucher is already claimed.');
        }

        if ($this->issuedToEmail !== $transferredFromEmail) {
            throw new LogicException('Voucher is not issued to the user.');
        }

        $this->issuedToEmail = $issuedToEmail;
        $this->code = $newCode;

        $this->record(new VoucherTransferred(
            $transferredFromEmail,
            $issuedToEmail,
            $occurredOn,
        ));
    }
}
