<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class VerifyUserEmail
{
    public function __construct(
        private string $emailVerificationSlug,
    ) {
    }

    public function getEmailVerificationSlug(): string
    {
        return $this->emailVerificationSlug;
    }
}
