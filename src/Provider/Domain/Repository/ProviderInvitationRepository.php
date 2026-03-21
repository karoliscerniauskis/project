<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\ProviderInvitation;
use App\Shared\Domain\Id\ProviderId;

interface ProviderInvitationRepository
{
    public function save(ProviderInvitation $invitation): void;

    public function findBySlug(string $slug): ?ProviderInvitation;

    public function findPendingByProviderIdAndEmail(ProviderId $providerId, string $email): ?ProviderInvitation;

    public function existsAcceptedByProviderIdAndEmail(ProviderId $providerId, string $email): bool;
}
