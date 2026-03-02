<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;

final class ProviderUser extends AbstractAggregateRoot
{
    private string $id;
    private string $providerId;
    private string $userId;

    private function __construct()
    {
        parent::__construct();
    }

    public static function assign(
        string $id,
        string $providerId,
        string $userId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;

        return $self;
    }

    public static function reconstitute(
        string $id,
        string $providerId,
        string $userId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;

        return $self;
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
