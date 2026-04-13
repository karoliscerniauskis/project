<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class VoucherCreated extends AbstractDomainEvent
{
    public function __construct(
        private string $providerId,
        private string $issuedToEmail,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getIssuedToEmail(): string
    {
        return $this->issuedToEmail;
    }

    public function toArray(): array
    {
        return [
            'providerId' => $this->providerId,
            'issuedToEmail' => $this->issuedToEmail,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
