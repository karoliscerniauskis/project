<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class UserEmailChangeRequested extends AbstractDomainEvent
{
    public function __construct(
        private string $email,
        private string $emailVerificationSlug,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEmailVerificationSlug(): string
    {
        return $this->emailVerificationSlug;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'emailVerificationSlug' => $this->emailVerificationSlug,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
