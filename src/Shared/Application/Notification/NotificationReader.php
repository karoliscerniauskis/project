<?php

declare(strict_types=1);

namespace App\Shared\Application\Notification;

use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\View\NotificationsView;

interface NotificationReader
{
    public function findByUserId(UserId $userId): NotificationsView;

    public function countUnreadByUserId(UserId $userId): int;
}
