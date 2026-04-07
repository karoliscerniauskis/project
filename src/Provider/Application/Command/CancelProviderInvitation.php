<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class CancelProviderInvitation
{
    public function __construct(
        private ProviderId $providerId,
        private string $email,
        private UserId $userId,
    ) {
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }
}
