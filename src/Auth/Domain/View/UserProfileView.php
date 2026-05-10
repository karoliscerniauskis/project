<?php

declare(strict_types=1);

namespace App\Auth\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{email: string, emailBreachCheckEnabled: bool, roles: string[]}>
 */
final readonly class UserProfileView implements ArrayableView
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        public string $email,
        public bool $emailBreachCheckEnabled,
        public array $roles,
    ) {
    }

    /**
     * @return array{email: string, emailBreachCheckEnabled: bool, roles: string[]}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'emailBreachCheckEnabled' => $this->emailBreachCheckEnabled,
            'roles' => $this->roles,
        ];
    }
}
