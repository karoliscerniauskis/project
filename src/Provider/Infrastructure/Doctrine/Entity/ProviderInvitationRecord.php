<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'provider_invitation')]
#[ORM\UniqueConstraint(name: 'uniq_provider_invitation_slug', columns: ['slug'])]
class ProviderInvitationRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'provider_id', type: 'uuid')]
    private string $providerId;

    #[ORM\Column(type: 'string')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $role;

    #[ORM\Column(type: 'string')]
    private string $slug;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\Column(name: 'invited_by_user_id', type: 'uuid')]
    private string $invitedByUserId;

    #[ORM\Column(name: 'accepted_user_id', type: 'uuid', nullable: true)]
    private ?string $acceptedUserId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'accepted_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $acceptedAt;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    public function __construct(
        string $id,
        string $providerId,
        string $email,
        string $role,
        string $slug,
        string $status,
        string $invitedByUserId,
        ?string $acceptedUserId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $acceptedAt,
        DateTimeImmutable $expiresAt,
    ) {
        $this->id = $id;
        $this->providerId = $providerId;
        $this->email = $email;
        $this->role = $role;
        $this->slug = $slug;
        $this->status = $status;
        $this->invitedByUserId = $invitedByUserId;
        $this->acceptedUserId = $acceptedUserId;
        $this->createdAt = $createdAt;
        $this->acceptedAt = $acceptedAt;
        $this->expiresAt = $expiresAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getInvitedByUserId(): string
    {
        return $this->invitedByUserId;
    }

    public function getAcceptedUserId(): ?string
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

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setAcceptedUserId(?string $acceptedUserId): self
    {
        $this->acceptedUserId = $acceptedUserId;

        return $this;
    }

    public function setAcceptedAt(?DateTimeImmutable $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }
}
