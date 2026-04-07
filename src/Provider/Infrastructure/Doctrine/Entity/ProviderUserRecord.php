<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'provider_user')]
#[ORM\UniqueConstraint(name: 'uniq_provider_user_provider_id_user_id', columns: ['provider_id', 'user_id'])]
class ProviderUserRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'provider_id', type: 'uuid')]
    private string $providerId;

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: 'string')]
    private string $role;

    #[ORM\Column(type: 'string')]
    private string $status;

    public function __construct(
        string $id,
        string $providerId,
        string $userId,
        string $role,
        string $status,
    ) {
        $this->id = $id;
        $this->providerId = $providerId;
        $this->userId = $userId;
        $this->role = $role;
        $this->status = $status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
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
