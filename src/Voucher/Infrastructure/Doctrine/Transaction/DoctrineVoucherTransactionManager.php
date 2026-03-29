<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Transaction;

use App\Shared\Application\Transaction\TransactionManager;
use App\Voucher\Application\Exception\VoucherCodeAlreadyExists;
use App\Voucher\Application\Transaction\VoucherTransactionManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Throwable;

final readonly class DoctrineVoucherTransactionManager implements VoucherTransactionManager
{
    public function __construct(
        private TransactionManager $transactionManager,
    ) {
    }

    public function transactional(callable $callback): mixed
    {
        try {
            return $this->transactionManager->transactional($callback);
        } catch (Throwable $throwable) {
            if ($throwable instanceof UniqueConstraintViolationException) {
                throw new VoucherCodeAlreadyExists(previous: $throwable);
            }

            throw $throwable;
        }
    }
}
