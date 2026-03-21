<?php

declare(strict_types=1);

namespace App\Provider\Application\Command;

final readonly class InviteProviderUser
{
    public function __construct(
        private string $providerId,
        private string $invitedByUserId,
        private string $email,
    ) {
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getInvitedByUserId(): string
    {
        return $this->invitedByUserId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
