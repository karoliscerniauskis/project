<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;

interface OutboxDomainEventFactory
{
    public function supports(string $eventName): bool;

    public function fromRecord(OutboxMessageRecord $record): DomainEvent;
}
