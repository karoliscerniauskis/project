<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'provider_link')]
#[ORM\UniqueConstraint(name: 'UNIQ_PROVIDER_LINK', columns: ['provider_id', 'linked_provider_id'])]
class ProviderLinkRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'provider_id', type: 'uuid')]
    private string $providerId;

    #[ORM\Column(name: 'linked_provider_id', type: 'uuid')]
    private string $linkedProviderId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $providerId,
        string $linkedProviderId,
        DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->providerId = $providerId;
        $this->linkedProviderId = $linkedProviderId;
        $this->createdAt = $createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getLinkedProviderId(): string
    {
        return $this->linkedProviderId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
