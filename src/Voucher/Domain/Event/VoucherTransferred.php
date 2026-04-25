<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class VoucherTransferred extends AbstractDomainEvent
{
    public function __construct(
        private string $transferredFromEmail,
        private string $transferredToEmail,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getTransferredFromEmail(): string
    {
        return $this->transferredFromEmail;
    }

    public function getTransferredToEmail(): string
    {
        return $this->transferredToEmail;
    }

    public function toArray(): array
    {
        return [
            'transferredFromEmail' => $this->transferredFromEmail,
            'transferredToEmail' => $this->transferredToEmail,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
