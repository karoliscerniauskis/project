<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class ChangeUserEmail
{
    public function __construct(
        private string $userId,
        private string $newEmail,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }
}
