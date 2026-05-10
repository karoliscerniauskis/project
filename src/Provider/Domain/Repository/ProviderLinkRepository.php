<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\ProviderLink;
use App\Shared\Domain\Id\ProviderId;

interface ProviderLinkRepository
{
    public function save(ProviderLink $providerLink): void;

    public function delete(ProviderLink $providerLink): void;

    public function findByProviderAndLinkedProvider(ProviderId $providerId, ProviderId $linkedProviderId): ?ProviderLink;

    /**
     * @return ProviderId[]
     */
    public function getLinkedProviderIds(ProviderId $providerId): array;
}
