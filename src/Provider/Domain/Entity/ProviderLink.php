<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderLinkId;
use DateTimeImmutable;

final class ProviderLink
{
    private ProviderLinkId $id;
    private ProviderId $providerId;
    private ProviderId $linkedProviderId;
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function create(
        ProviderLinkId $id,
        ProviderId $providerId,
        ProviderId $linkedProviderId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->linkedProviderId = $linkedProviderId;
        $self->createdAt = new DateTimeImmutable();

        return $self;
    }

    public static function reconstitute(
        ProviderLinkId $id,
        ProviderId $providerId,
        ProviderId $linkedProviderId,
        DateTimeImmutable $createdAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->linkedProviderId = $linkedProviderId;
        $self->createdAt = $createdAt;

        return $self;
    }

    public function getId(): ProviderLinkId
    {
        return $this->id;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getLinkedProviderId(): ProviderId
    {
        return $this->linkedProviderId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
