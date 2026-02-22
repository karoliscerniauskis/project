<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Event\DomainEvents;

final class User
{
    private DomainEvents $events;

    private function __construct()
    {
        $this->events = new DomainEvents();
    }

    /** @return DomainEvent[] */
    public function pullDomainEvents(): array
    {
        return $this->events->pull();
    }

    private function record(DomainEvent $event): void
    {
        $this->events->record($event);
    }
}
