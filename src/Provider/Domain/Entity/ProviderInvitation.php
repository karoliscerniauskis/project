<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;

final class ProviderInvitation extends AbstractAggregateRoot
{
    private ProviderInvitationId $id;
    private ProviderId $providerId;
    private string $email;
    private ProviderUserRole $role;
    private string $slug;
    private ProviderInvitationStatus $status;
    private UserId $invitedByUserId;
    private ?UserId $acceptedUserId = null;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $acceptedAt = null;
    private DateTimeImmutable $expiresAt;

    private function __construct()
    {
        parent::__construct();
    }

    public static function invite(
        ProviderInvitationId $id,
        ProviderId $providerId,
        string $email,
        ProviderUserRole $role,
        string $slug,
        UserId $invitedByUserId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->email = $email;
        $self->role = $role;
        $self->slug = $slug;
        $self->status = ProviderInvitationStatus::Pending;
        $self->invitedByUserId = $invitedByUserId;
        $self->createdAt = $createdAt;
        $self->expiresAt = $expiresAt;
        $self->record(new ProviderInvitationCreated(
            $id->toString(),
            $email,
            $slug,
            $createdAt,
        ));

        return $self;
    }

    public static function reconstitute(
        ProviderInvitationId $id,
        ProviderId $providerId,
        string $email,
        ProviderUserRole $role,
        string $slug,
        ProviderInvitationStatus $status,
        UserId $invitedByUserId,
        ?UserId $acceptedUserId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $acceptedAt,
        DateTimeImmutable $expiresAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->email = $email;
        $self->role = $role;
        $self->slug = $slug;
        $self->status = $status;
        $self->invitedByUserId = $invitedByUserId;
        $self->acceptedUserId = $acceptedUserId;
        $self->createdAt = $createdAt;
        $self->acceptedAt = $acceptedAt;
        $self->expiresAt = $expiresAt;

        return $self;
    }

    public function getId(): ProviderInvitationId
    {
        return $this->id;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): ProviderUserRole
    {
        return $this->role;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getStatus(): ProviderInvitationStatus
    {
        return $this->status;
    }

    public function getInvitedByUserId(): UserId
    {
        return $this->invitedByUserId;
    }

    public function getAcceptedUserId(): ?UserId
    {
        return $this->acceptedUserId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAcceptedAt(): ?DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function accept(UserId $acceptedUserId, DateTimeImmutable $acceptedAt): void
    {
        if ($this->status !== ProviderInvitationStatus::Pending) {
            return;
        }

        if ($acceptedAt > $this->expiresAt) {
            $this->status = ProviderInvitationStatus::Expired;

            return;
        }

        $this->acceptedUserId = $acceptedUserId;
        $this->acceptedAt = $acceptedAt;
        $this->status = ProviderInvitationStatus::Accepted;
    }

    public function cancel(): void
    {
        if ($this->status !== ProviderInvitationStatus::Pending) {
            return;
        }

        $this->status = ProviderInvitationStatus::Cancelled;
    }
}
