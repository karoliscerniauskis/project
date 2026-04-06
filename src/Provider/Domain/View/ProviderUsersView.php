<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

final readonly class ProviderUsersView
{
    /**
     * @param ProviderUserView[] $users
     */
    public function __construct(
        private array $users,
    ) {
    }

    /**
     * @return ProviderUserView[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }
}
