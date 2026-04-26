<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Notification;

use App\Shared\Application\Notification\NotificationMarker;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use App\Shared\Infrastructure\Doctrine\Notification\Entity\NotificationRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNotificationMarker implements NotificationMarker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Clock $clock,
    ) {
    }

    public function markAsRead(string $notificationId, UserId $userId): bool
    {
        $notification = $this->entityManager
            ->getRepository(NotificationRecord::class)
            ->findOneBy([
                'id' => $notificationId,
                'userId' => $userId->toString(),
            ]);

        if (!$notification instanceof NotificationRecord) {
            return false;
        }

        $notification->markAsRead($this->clock->now());
        $this->entityManager->flush();

        return true;
    }
}
