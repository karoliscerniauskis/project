<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Event\ProviderCreated;
use App\Provider\Domain\Event\ProviderDeactivated;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use DateTimeImmutable;
use LogicException;

final class Provider extends AbstractAggregateRoot
{
    private ProviderId $id;
    private string $name;
    private ProviderStatus $status;
    private ?int $claimReminderAfterDays = null;
    private ?int $expiryReminderBeforeDays = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(
        ProviderId $id,
        string $name,
        ProviderStatus $status,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->status = $status;
        $self->claimReminderAfterDays = null;
        $self->expiryReminderBeforeDays = null;
        $self->record(new ProviderCreated(
            $id->toString(),
            $name,
            $occurredOn,
        ));

        return $self;
    }

    public static function reconstitute(
        ProviderId $id,
        string $name,
        ProviderStatus $status,
        ?int $claimReminderAfterDays = null,
        ?int $expiryReminderBeforeDays = null,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->status = $status;
        $self->claimReminderAfterDays = $claimReminderAfterDays;
        $self->expiryReminderBeforeDays = $expiryReminderBeforeDays;

        return $self;
    }

    public function getId(): ProviderId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): ProviderStatus
    {
        return $this->status;
    }

    public function getClaimReminderAfterDays(): ?int
    {
        return $this->claimReminderAfterDays;
    }

    public function getExpiryReminderBeforeDays(): ?int
    {
        return $this->expiryReminderBeforeDays;
    }

    public function configureReminderSettings(
        ?int $claimReminderAfterDays,
        ?int $expiryReminderBeforeDays,
    ): void {
        if ($claimReminderAfterDays !== null && $claimReminderAfterDays <= 0) {
            throw new LogicException('Claim reminder days must be positive.');
        }

        if ($expiryReminderBeforeDays !== null && $expiryReminderBeforeDays <= 0) {
            throw new LogicException('Expiry reminder days must be positive.');
        }

        $this->claimReminderAfterDays = $claimReminderAfterDays;
        $this->expiryReminderBeforeDays = $expiryReminderBeforeDays;
    }

    public function approve(DateTimeImmutable $occurredOn): void
    {
        if ($this->status === ProviderStatus::Active) {
            return;
        }

        $this->status = ProviderStatus::Active;
        $this->record(new ProviderApproved(
            $this->id->toString(),
            $occurredOn,
        ));
    }

    public function deactivate(DateTimeImmutable $occurredOn): void
    {
        if ($this->status !== ProviderStatus::Active) {
            return;
        }

        $this->status = ProviderStatus::Inactive;
        $this->record(new ProviderDeactivated(
            $this->id->toString(),
            $this->name,
            $occurredOn,
        ));
    }

    public function isActive(): bool
    {
        return $this->status === ProviderStatus::Active;
    }
}
