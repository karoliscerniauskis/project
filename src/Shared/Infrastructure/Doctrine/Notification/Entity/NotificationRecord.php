<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Notification\Entity;

use App\Shared\Domain\View\NotificationView;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'notification')]
class NotificationRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: 'string', length: 100)]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $message;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'read_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        string $id,
        string $userId,
        string $type,
        string $title,
        string $message,
        array $payload,
        DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->payload = $payload;
        $this->createdAt = $createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function markAsRead(DateTimeImmutable $readAt): void
    {
        if ($this->readAt !== null) {
            return;
        }

        $this->readAt = $readAt;
    }

    public function toView(): NotificationView
    {
        return new NotificationView(
            $this->id,
            $this->type,
            $this->title,
            $this->message,
            $this->payload,
            $this->readAt,
            $this->createdAt,
        );
    }
}
