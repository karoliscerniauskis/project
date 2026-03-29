<?php

declare(strict_types=1);

namespace App\Shared\Application\Outbox;

interface OutboxProcessor
{
    public function processPending(): void;
}
