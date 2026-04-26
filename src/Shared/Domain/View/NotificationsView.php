<?php

declare(strict_types=1);

namespace App\Shared\Domain\View;

/**
 * @implements ArrayableView<array<array{id: string, type: string, title: string, message: string, payload: array<string, mixed>, readAt: string|null, createdAt: string}>>
 */
final readonly class NotificationsView implements ArrayableView
{
    /**
     * @param NotificationView[] $notifications
     */
    public function __construct(
        private array $notifications,
    ) {
    }

    /**
     * @return array<array{id: string, type: string, title: string, message: string, payload: array<string, mixed>, readAt: string|null, createdAt: string}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (NotificationView $notification): array => $notification->toArray(),
            $this->notifications,
        );
    }
}
