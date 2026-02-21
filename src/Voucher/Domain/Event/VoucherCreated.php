<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;

final readonly class VoucherCreated extends AbstractDomainEvent
{
}
