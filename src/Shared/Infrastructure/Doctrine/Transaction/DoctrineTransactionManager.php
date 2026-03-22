<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Transaction;

use App\Shared\Application\Transaction\TransactionManager;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionManager implements TransactionManager
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
