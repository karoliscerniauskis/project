<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class VoucherClaimed extends AbstractDomainEvent
{
    public function __construct(
        private string $voucherCode,
        private string $issuedToEmail,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getVoucherCode(): string
    {
        return $this->voucherCode;
    }

    public function getIssuedToEmail(): string
    {
        return $this->issuedToEmail;
    }

    public function toArray(): array
    {
        return [
            'voucherCode' => $this->voucherCode,
            'issuedToEmail' => $this->issuedToEmail,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
