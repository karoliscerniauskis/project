<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class PasswordResetRequested extends AbstractDomainEvent
{
    public function __construct(
        private string $email,
        private string $resetToken,
        DateTimeImmutable $occurredOn,
    ) {
        parent::__construct($occurredOn);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'resetToken' => $this->resetToken,
            'occurredOn' => $this->occurredOn->format(DateTimeInterface::ATOM),
        ];
    }
}
