<?php

declare(strict_types=1);

namespace App\Provider\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class ProviderUserRemoved extends AbstractDomainEvent
{
    public function __construct(
        private string $providerId,
        private string $userId,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return [
            'providerId' => $this->providerId,
            'userId' => $this->userId,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
