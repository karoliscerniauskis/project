<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messenger;

use App\Shared\Application\Event\DomainEventDispatcher;
use App\Shared\Domain\Event\DomainEvent;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerDomainEventDispatcher implements DomainEventDispatcher
{
    public function __construct(private MessageBusInterface $eventBus)
    {
    }

    public function dispatch(DomainEvent $event): void
    {
        $this->eventBus->dispatch($event);
    }
}
