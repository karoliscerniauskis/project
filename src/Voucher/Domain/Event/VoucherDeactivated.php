<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeInterface;

final readonly class VoucherDeactivated extends AbstractDomainEvent
{
    public function toArray(): array
    {
        return [
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
