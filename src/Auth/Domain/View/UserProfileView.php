<?php

declare(strict_types=1);

namespace App\Auth\Domain\View;

final readonly class UserProfileView
{
    public function __construct(
        public string $email,
        public bool $emailBreachCheckEnabled,
    ) {
    }
}
