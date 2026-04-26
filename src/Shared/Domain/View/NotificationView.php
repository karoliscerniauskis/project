<?php

declare(strict_types=1);

namespace App\Shared\Domain\View;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * @implements ArrayableView<array{id: string, type: string, title: string, message: string, payload: array<string, mixed>, readAt: string|null, createdAt: string}>
 */
final readonly class NotificationView implements ArrayableView
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $id,
        private string $type,
        private string $title,
        private string $message,
        private array $payload,
        private ?DateTimeImmutable $readAt,
        private DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @return array{id: string, type: string, title: string, message: string, payload: array<string, mixed>, readAt: string|null, createdAt: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'payload' => $this->payload,
            'readAt' => $this->readAt?->format(DateTimeInterface::ATOM),
            'createdAt' => $this->createdAt->format(DateTimeInterface::ATOM),
        ];
    }
}
