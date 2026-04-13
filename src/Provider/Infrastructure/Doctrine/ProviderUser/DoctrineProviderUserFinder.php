<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\ProviderUser;

use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\ProviderUser\ProviderUserFinder;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

final readonly class DoctrineProviderUserFinder implements ProviderUserFinder
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
    ) {
    }

    public function findIdByProviderIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderUserId
    {
        $providerUser = $this->providerUserRepository->findByProviderIdAndUserId($providerId, $userId);

        return $providerUser?->getId();
    }
}
