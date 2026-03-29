<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Outbox;

use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\AbstractOutboxDomainEventFactory;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use App\Shared\Infrastructure\Doctrine\Outbox\OutboxDomainEventFactory;
use RuntimeException;

final readonly class ProviderOutboxDomainEventFactory extends AbstractOutboxDomainEventFactory implements OutboxDomainEventFactory
{
    public function supports(string $eventName): bool
    {
        return in_array($eventName, [
            ProviderApproved::class,
            ProviderInvitationCreated::class,
        ], true);
    }

    public function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        return match ($record->getEventName()) {
            ProviderApproved::class => new ProviderApproved(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $record->getOccurredAt(),
            ),
            ProviderInvitationCreated::class => new ProviderInvitationCreated(
                $this->stringPayloadValue($record->getPayload(), 'providerInvitationId'),
                $this->stringPayloadValue($record->getPayload(), 'email'),
                $this->stringPayloadValue($record->getPayload(), 'slug'),
                $record->getOccurredAt(),
            ),
            default => throw new RuntimeException('Unsupported provider outbox event: '.$record->getEventName()),
        };
    }
}
