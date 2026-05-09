<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Persistence;

use App\Shared\Application\Persistence\Flusher;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFlusher implements Flusher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
