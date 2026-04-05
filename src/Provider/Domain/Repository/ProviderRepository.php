<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\Provider;
use App\Shared\Domain\Id\ProviderId;

interface ProviderRepository
{
    public function save(Provider $provider): void;

    public function findById(ProviderId $id): ?Provider;

    public function existsByName(string $name): bool;
}
