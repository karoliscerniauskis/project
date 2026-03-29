<?php

declare(strict_types=1);

namespace App\Shared\Application\Outbox;

use App\Shared\Domain\Event\DomainEvent;

interface OutboxWriter
{
    /**
     * @param DomainEvent[] $events
     */
    public function storeAll(array $events): void;
}
