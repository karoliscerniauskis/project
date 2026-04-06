<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;
use DateTimeImmutable;

/**
 * @implements ArrayableView<array{email: string, createdAt: string, expiresAt: string}>
 */
final readonly class ProviderInvitationView implements ArrayableView
{
    public function __construct(
        private string $email,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $expiresAt,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return array{email: string, createdAt: string, expiresAt: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
            'createdAt' => $this->getCreatedAt()->format(DATE_ATOM),
            'expiresAt' => $this->getExpiresAt()->format(DATE_ATOM),
        ];
    }
}
