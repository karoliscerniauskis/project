<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

abstract class AbstractAggregateRoot
{
    private DomainEvents $events;

    protected function __construct()
    {
        $this->events = new DomainEvents();
    }

    /** @return DomainEvent[] */
    final public function pullDomainEvents(): array
    {
        return $this->events->pull();
    }

    final protected function record(DomainEvent $event): void
    {
        $this->events->record($event);
    }
}
