<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Outbox;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Doctrine\Outbox\AbstractOutboxDomainEventFactory;
use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use App\Shared\Infrastructure\Doctrine\Outbox\OutboxDomainEventFactory;
use App\Voucher\Domain\Event\VoucherClaimed;
use App\Voucher\Domain\Event\VoucherCreated;
use App\Voucher\Domain\Event\VoucherDeactivated;
use App\Voucher\Domain\Event\VoucherExpired;
use App\Voucher\Domain\Event\VoucherTransferred;
use RuntimeException;

final readonly class VoucherOutboxDomainEventFactory extends AbstractOutboxDomainEventFactory implements OutboxDomainEventFactory
{
    public function supports(string $eventName): bool
    {
        return in_array($eventName, [
            VoucherClaimed::class,
            VoucherCreated::class,
            VoucherDeactivated::class,
            VoucherExpired::class,
            VoucherTransferred::class,
        ], true);
    }

    public function fromRecord(OutboxMessageRecord $record): DomainEvent
    {
        return match ($record->getEventName()) {
            VoucherClaimed::class => new VoucherClaimed(
                $this->stringPayloadValue($record->getPayload(), 'providerId'),
                $this->stringPayloadValue($record->getPayload(), 'voucherCode'),
                $this->stringPayloadValue($record->getPayload(), 'issuedToEmail'),
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
            VoucherTransferred::class => new VoucherTransferred(
                $this->stringPayloadValue($record->getPayload(), 'transferredFromEmail'),
                $this->stringPayloadValue($record->getPayload(), 'transferredToEmail'),
                $record->getOccurredAt(),
            ),
            default => throw new RuntimeException('Unsupported voucher outbox event: '.$record->getEventName()),
        };
    }
}
