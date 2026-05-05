<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Event\ProviderCreated;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use DateTimeImmutable;

final class Provider extends AbstractAggregateRoot
{
    private ProviderId $id;
    private string $name;
    private ProviderStatus $status;

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
    ): self {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->status = $status;

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

    public function deactivate(): void
    {
        if ($this->status !== ProviderStatus::Active) {
            return;
        }

        $this->status = ProviderStatus::Inactive;
    }

    public function isActive(): bool
    {
        return $this->status === ProviderStatus::Active;
    }
}
