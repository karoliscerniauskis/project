<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, email: string, role: string, status: string}>
 */
final readonly class ProviderUserView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $email,
        private string $role,
        private string $status,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
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
     * @return array{id: string, email: string, role: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'status' => $this->getStatus(),
        ];
    }
}
