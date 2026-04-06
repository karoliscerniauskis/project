<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array<array{email: string, role: string}>>
 */
final readonly class ProviderUsersView implements ArrayableView
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

    /**
     * @return array<array{email: string, role: string}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ProviderUserView $user): array => $user->toArray(),
            $this->users,
        );
    }
}
