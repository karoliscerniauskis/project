<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'provider_user')]
#[ORM\UniqueConstraint(name: 'uniq_provider_user_user_id', columns: ['user_id'])]
class ProviderUserRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'provider_id', type: 'uuid')]
    private string $providerId;

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private string $userId;

    public function __construct(
        string $id,
        string $providerId,
        string $userId,
    ) {
        $this->id = $id;
        $this->providerId = $providerId;
        $this->userId = $userId;
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
}
