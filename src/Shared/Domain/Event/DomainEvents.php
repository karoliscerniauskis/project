<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

final class DomainEvents
{
    /** @var DomainEvent[] */
    private array $events = [];

    public function record(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return DomainEvent[]
     */
    public function pull(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
