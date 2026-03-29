<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox;

use App\Shared\Application\Event\DomainEventDispatcher;
use App\Shared\Application\Outbox\OutboxProcessor;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use App\Shared\Infrastructure\Doctrine\Outbox\Repository\DoctrineOutboxMessageRepository;
use RuntimeException;

final readonly class DoctrineOutboxProcessor implements OutboxProcessor
{
    /**
     * @param iterable<OutboxDomainEventFactory> $factories
     */
    public function __construct(
        private TransactionManager $transactionManager,
        private DoctrineOutboxMessageRepository $outboxMessageRepository,
        private DomainEventDispatcher $domainEventDispatcher,
        private Clock $clock,
        private iterable $factories,
    ) {
    }

    public function processPending(): void
    {
        $this->transactionManager->transactional(function (): void {
            foreach ($this->outboxMessageRepository->findPending() as $record) {
                $record->markProcessing($this->clock->now());
                $this->domainEventDispatcher->dispatch($this->fromRecord($record));
                $record->markProcessed($this->clock->now());
            }
        });
    }

    private function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($record->getEventName())) {
                return $factory->fromRecord($record);
            }
        }

        throw new RuntimeException('No outbox factory found for event: '.$record->getEventName());
    }
}
