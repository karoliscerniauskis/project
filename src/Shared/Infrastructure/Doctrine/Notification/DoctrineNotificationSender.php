<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Notification;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\Infrastructure\Doctrine\Notification\Entity\NotificationRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNotificationSender implements NotificationSender
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UuidCreator $uuidCreator,
        private Clock $clock,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function send(
        UserId $userId,
        string $type,
        string $title,
        string $message,
        array $payload = [],
    ): void {
        $notification = new NotificationRecord(
            $this->uuidCreator->create(),
            $userId->toString(),
            $type,
            $title,
            $message,
            $payload,
            $this->clock->now(),
        );
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
