<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class ChangeUserPassword
{
    public function __construct(
        private string $userId,
        private string $currentPassword,
        private string $newPassword,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
