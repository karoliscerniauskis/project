<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Outbox;

use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Auth\Domain\Event\UserPasswordChanged;
use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\AbstractOutboxDomainEventFactory;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use App\Shared\Infrastructure\Doctrine\Outbox\OutboxDomainEventFactory;
use RuntimeException;

final readonly class AuthOutboxDomainEventFactory extends AbstractOutboxDomainEventFactory implements OutboxDomainEventFactory
{
    public function supports(string $eventName): bool
    {
        return in_array($eventName, [
            UserRegistered::class,
            UserEmailChangeRequested::class,
            UserPasswordChanged::class,
        ], true);
    }

    public function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        return match ($record->getEventName()) {
            UserRegistered::class => new UserRegistered(
                $this->stringPayloadValue($record->getPayload(), 'email'),
                $this->stringPayloadValue($record->getPayload(), 'emailVerificationSlug'),
                $record->getOccurredAt(),
            ),
            UserEmailChangeRequested::class => new UserEmailChangeRequested(
                $this->stringPayloadValue($record->getPayload(), 'email'),
                $this->stringPayloadValue($record->getPayload(), 'emailVerificationSlug'),
                $record->getOccurredAt(),
            ),
            UserPasswordChanged::class => new UserPasswordChanged($record->getOccurredAt()),
            default => throw new RuntimeException('Unsupported auth outbox event: '.$record->getEventName()),
        };
    }
}
