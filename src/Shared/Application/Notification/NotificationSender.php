<?php

declare(strict_types=1);

namespace App\Shared\Application\Notification;

use App\Shared\Domain\Id\UserId;

interface NotificationSender
{
    /**
     * @param array<string, mixed> $payload
     */
    public function send(
        UserId $userId,
        string $type,
        string $title,
        string $message,
        array $payload = [],
    ): void;
}
