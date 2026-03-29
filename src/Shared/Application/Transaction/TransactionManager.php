<?php

declare(strict_types=1);

namespace App\Shared\Application\Transaction;

interface TransactionManager
{
    public function transactional(callable $callback): mixed;
}
