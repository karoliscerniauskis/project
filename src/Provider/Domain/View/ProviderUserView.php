<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{email: string, role: string}>
 */
final readonly class ProviderUserView implements ArrayableView
{
    public function __construct(
        private string $email,
        private string $role,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return array{email: string, role: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
        ];
    }
}
