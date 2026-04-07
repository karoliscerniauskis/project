<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{email: string, role: string, status: string}>
 */
final readonly class ProviderUserView implements ArrayableView
{
    public function __construct(
        private string $email,
        private string $role,
        private string $status,
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

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array{email: string, role: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'status' => $this->getStatus(),
        ];
    }
}
