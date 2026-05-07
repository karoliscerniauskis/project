<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Outbox;

use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Event\ProviderCreated;
use App\Provider\Domain\Event\ProviderDeactivated;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Provider\Domain\Event\ProviderUserRemoved;
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
            ProviderCreated::class,
            ProviderDeactivated::class,
            ProviderUserRemoved::class,
        ], true);
    }

    public function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        return match ($record->getEventName()) {
            ProviderApproved::class => new ProviderApproved(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $record->getOccurredAt(),
            ),
            ProviderCreated::class => new ProviderCreated(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'providerName'),
                $record->getOccurredAt(),
            ),
            ProviderDeactivated::class => new ProviderDeactivated(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'providerName'),
                $record->getOccurredAt(),
            ),
            ProviderInvitationCreated::class => new ProviderInvitationCreated(
                $this->stringPayloadValue($record->getPayload(), 'providerInvitationId'),
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'email'),
                $this->stringPayloadValue($record->getPayload(), 'slug'),
                $record->getOccurredAt(),
            ),
            ProviderUserRemoved::class => new ProviderUserRemoved(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'userId'),
                $record->getOccurredAt(),
            ),
            default => throw new RuntimeException('Unsupported provider outbox event: '.$record->getEventName()),
        };
    }
}
