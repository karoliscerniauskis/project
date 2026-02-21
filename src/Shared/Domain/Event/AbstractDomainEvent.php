<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use DateTimeImmutable;

abstract readonly class AbstractDomainEvent implements DomainEvent
{
    public function __construct(private DateTimeImmutable $occurredOn)
    {
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
