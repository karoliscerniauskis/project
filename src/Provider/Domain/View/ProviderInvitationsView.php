<?php

declare(strict_types=1);

namespace App\Provider\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array<array{email: string, createdAt: string, expiresAt: string}>>
 */
final readonly class ProviderInvitationsView implements ArrayableView
{
    /**
     * @param ProviderInvitationView[] $invitations
     */
    public function __construct(
        private array $invitations,
    ) {
    }

    /**
     * @return ProviderInvitationView[]
     */
    public function getInvitations(): array
    {
        return $this->invitations;
    }

    /**
     * @return array<array{email: string, createdAt: string, expiresAt: string}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ProviderInvitationView $invitation): array => $invitation->toArray(),
            $this->invitations,
        );
    }
}
