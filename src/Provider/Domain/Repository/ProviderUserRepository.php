<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\ProviderUser;

interface ProviderUserRepository
{
    public function save(ProviderUser $providerUser): void;

    public function findProviderIdByUserId(string $userId): ?string;
}
