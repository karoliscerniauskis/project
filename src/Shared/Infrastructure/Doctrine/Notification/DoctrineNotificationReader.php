<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Notification;

use App\Shared\Application\Notification\NotificationReader;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\View\NotificationsView;
use App\Shared\Infrastructure\Doctrine\Notification\Entity\NotificationRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNotificationReader implements NotificationReader
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUserId(UserId $userId): NotificationsView
    {
        $records = $this->entityManager
            ->getRepository(NotificationRecord::class)
            ->findBy(
                ['userId' => $userId->toString()],
                ['createdAt' => 'DESC'],
            );

        return new NotificationsView(
            array_map(
                static fn (NotificationRecord $record) => $record->toView(),
                $records,
            ),
        );
    }

    public function countUnreadByUserId(UserId $userId): int
    {
        return $this->entityManager
            ->getRepository(NotificationRecord::class)
            ->count([
                'userId' => $userId->toString(),
                'readAt' => null,
            ]);
    }
}
