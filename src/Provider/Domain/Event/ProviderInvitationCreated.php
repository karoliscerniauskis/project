<?php

declare(strict_types=1);

namespace App\Provider\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;

final readonly class ProviderInvitationCreated extends AbstractDomainEvent
{
    public function __construct(
        private string $providerInvitationId,
        private string $email,
        private string $slug,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getProviderInvitationId(): string
    {
        return $this->providerInvitationId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
