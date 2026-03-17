<?php

declare(strict_types=1);

namespace App\Provider\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;

final readonly class ProviderApproved extends AbstractDomainEvent
{
    public function __construct(
        private string $providerId,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }
}
