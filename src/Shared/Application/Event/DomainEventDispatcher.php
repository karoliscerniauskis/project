<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

use App\Shared\Domain\Event\DomainEvent;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent $event): void;

    /**
     * @param DomainEvent[] $events
     */
    public function dispatchAll(array $events): void;
}
