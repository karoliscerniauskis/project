<?php

declare(strict_types=1);

namespace App\Provider\Domain\Repository;

use App\Provider\Domain\Entity\Provider;

interface ProviderRepository
{
    public function save(Provider $provider): void;

    public function findById(string $id): ?Provider;
}
