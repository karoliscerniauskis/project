<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class ResetPassword
{
    public function __construct(
        private string $resetToken,
        private string $newPassword,
    ) {
    }

    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
