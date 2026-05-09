<?php

declare(strict_types=1);

namespace App\Auth\Application\EmailBreach;

final readonly class EmailBreachCheckResult
{
    public function __construct(
        private bool $breached,
        private int $breachCount,
    ) {
    }

    public function isBreached(): bool
    {
        return $this->breached;
    }

    public function getBreachCount(): int
    {
        return $this->breachCount;
    }
}
