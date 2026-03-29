<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox\Mapper;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;

final readonly class OutboxMessageRecordMapper
{
    public function __construct(
        private UuidCreator $uuidCreator,
    ) {
    }

    public function toRecord(DomainEvent $event): OutboxMessageRecord
    {
        return new OutboxMessageRecord(
            $this->uuidCreator->create(),
            $event::class,
            $event->toArray(),
            $event->getOccurredOn(),
        );
    }
}
