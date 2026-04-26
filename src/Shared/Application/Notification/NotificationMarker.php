<?php

declare(strict_types=1);

namespace App\Shared\Application\Notification;

use App\Shared\Domain\Id\UserId;

interface NotificationMarker
{
    public function markAsRead(string $notificationId, UserId $userId): bool;
}
