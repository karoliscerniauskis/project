<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Access;

use App\Provider\Domain\Access\ProviderAccessChecker;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class RepositoryProviderAccessChecker implements ProviderAccessChecker
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
    ) {
    }

    public function isMember(ProviderId $providerId, UserId $userId): bool
    {
        return $this->providerUserRepository->isMember($providerId, $userId);
    }

    public function isAdmin(ProviderId $providerId, UserId $userId): bool
    {
        return $this->providerUserRepository->isAdmin($providerId, $userId);
    }
}
