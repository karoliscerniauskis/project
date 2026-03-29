<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox;

use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\Mapper\OutboxMessageRecordMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOutboxWriter implements OutboxWriter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OutboxMessageRecordMapper $outboxMessageRecordMapper,
    ) {
    }

    /**
     * @param DomainEvent[] $events
     */
    public function storeAll(array $events): void
    {
        foreach ($events as $event) {
            $this->entityManager->persist($this->outboxMessageRecordMapper->toRecord($event));
        }
    }
}
