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
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final readonly class DoctrineOutboxProcessor implements OutboxProcessor
{
    private const int MAX_RETRY_COUNT = 3;

    /**
     * @param iterable<OutboxDomainEventFactory> $factories
     */
    public function __construct(
        private TransactionManager $transactionManager,
        private DoctrineOutboxMessageRepository $outboxMessageRepository,
        private DomainEventDispatcher $domainEventDispatcher,
        private Clock $clock,
        private iterable $factories,
        private LoggerInterface $logger,
    ) {
    }

    public function processPending(): void
    {
        $this->transactionManager->transactional(function (): void {
            foreach ($this->outboxMessageRepository->findPending() as $record) {
                try {
                    $this->processRecord($record);
                } catch (Throwable $exception) {
                    $this->handleFailedRecord($record, $exception);
                }
            }
        });
    }

    private function processRecord(OutboxMessageRecord $record): void
    {
        $this->transactionManager->transactional(function () use ($record): void {
            $record->markProcessing($this->clock->now());
            $this->domainEventDispatcher->dispatch($this->fromRecord($record));
            $record->markProcessed($this->clock->now());
        });
    }

    private function handleFailedRecord(OutboxMessageRecord $record, Throwable $exception): void
    {
        $this->logger->error('Outbox message processing failed.', [
            'outboxMessageId' => $record->getId(),
            'eventName' => $record->getEventName(),
            'retryCount' => $record->getRetryCount(),
            'exception' => $exception,
        ]);

        try {
            $this->transactionManager->transactional(function () use ($record): void {
                $record->incrementRetryCount();

                if ($record->getRetryCount() >= self::MAX_RETRY_COUNT) {
                    $record->markFailed(
                        $this->clock->now(),
                    );

                    return;
                }

                $record->releaseProcessing();
            });
        } catch (Throwable $releaseException) {
            $this->logger->error('Failed to update failed outbox message state.', [
                'outboxMessageId' => $record->getId(),
                'eventName' => $record->getEventName(),
                'exception' => $releaseException,
            ]);
        }
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
