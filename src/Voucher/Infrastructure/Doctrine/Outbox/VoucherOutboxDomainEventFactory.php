<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Outbox;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\AbstractOutboxDomainEventFactory;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use App\Shared\Infrastructure\Doctrine\Outbox\OutboxDomainEventFactory;
use App\Voucher\Domain\Event\VoucherActivated;
use App\Voucher\Domain\Event\VoucherCreated;
use App\Voucher\Domain\Event\VoucherDeactivated;
use App\Voucher\Domain\Event\VoucherExpired;
use RuntimeException;

final readonly class VoucherOutboxDomainEventFactory extends AbstractOutboxDomainEventFactory implements OutboxDomainEventFactory
{
    public function supports(string $eventName): bool
    {
        return in_array($eventName, [
            VoucherActivated::class,
            VoucherCreated::class,
            VoucherDeactivated::class,
            VoucherExpired::class,
        ], true);
    }

    public function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        return match ($record->getEventName()) {
            VoucherActivated::class => new VoucherActivated(
                $record->getOccurredAt(),
            ),
            VoucherCreated::class => new VoucherCreated(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'issuedToEmail'),
                $record->getOccurredAt(),
            ),
            VoucherDeactivated::class => new VoucherDeactivated(
                $record->getOccurredAt(),
            ),
            VoucherExpired::class => new VoucherExpired(
                $record->getOccurredAt(),
            ),
            default => throw new RuntimeException('Unsupported voucher outbox event: '.$record->getEventName()),
        };
    }
}
