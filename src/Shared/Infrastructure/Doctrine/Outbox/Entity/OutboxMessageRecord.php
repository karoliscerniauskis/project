<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'outbox_message')]
class OutboxMessageRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'string')]
    private string $eventName;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'occurred_at', type: 'datetime_immutable')]
    private DateTimeImmutable $occurredAt;

    #[ORM\Column(name: 'processing_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $processingAt = null;

    #[ORM\Column(name: 'processed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $processedAt = null;

    #[ORM\Column(name: 'failed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $failedAt = null;

    #[ORM\Column(name: 'retry_count', type: 'integer')]
    private int $retryCount = 0;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        string $id,
        string $eventName,
        array $payload,
        DateTimeImmutable $occurredAt,
    ) {
        $this->id = $id;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function getProcessingAt(): ?DateTimeImmutable
    {
        return $this->processingAt;
    }

    public function getFailedAt(): ?DateTimeImmutable
    {
        return $this->failedAt;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function markProcessing(DateTimeImmutable $processingAt): void
    {
        $this->processingAt = $processingAt;
    }

    public function markProcessed(DateTimeImmutable $processedAt): void
    {
        $this->processedAt = $processedAt;
        $this->processingAt = null;
    }

    public function markFailed(DateTimeImmutable $failedAt): void
    {
        $this->failedAt = $failedAt;
        $this->processingAt = null;
    }

    public function releaseProcessing(): void
    {
        $this->processingAt = null;
    }

    public function incrementRetryCount(): void
    {
        ++$this->retryCount;
    }
}
