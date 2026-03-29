<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Transaction;

use App\Shared\Application\Transaction\TransactionManager;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

final readonly class DoctrineTransactionManager implements TransactionManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function transactional(callable $callback): mixed
    {
        $this->entityManager->beginTransaction();

        try {
            $result = $callback();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $result;
        } catch (Throwable $throwable) {
            $this->entityManager->rollback();

            throw $throwable;
        }
    }
}
