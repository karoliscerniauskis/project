<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox\Repository;

use App\Shared\Infrastructure\Doctrine\Outbox\Entity\OutboxMessageRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineOutboxMessageRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return OutboxMessageRecord[]
     */
    public function findPending(int $limit = 100): array
    {
        return $this->entityManager->getRepository(OutboxMessageRecord::class)->findBy(
            [
                'processedAt' => null,
                'processingAt' => null,
                'failedAt' => null,
            ],
            ['occurredAt' => 'ASC'],
            $limit,
        );
    }

    public function save(OutboxMessageRecord $record): void
    {
        $this->entityManager->persist($record);
    }
}
