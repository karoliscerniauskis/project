<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Exception\ProviderAdminRoleRequired;
use App\Provider\Application\Query\GetAvailableProvidersToLink;
use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\View\AvailableProvidersView;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class GetAvailableProvidersToLinkHandler
{
    public function __construct(
        private ProviderReadRepository $providerReadRepository,
        private ProviderUserRepository $providerUserRepository,
    ) {
    }

    public function __invoke(GetAvailableProvidersToLink $query): AvailableProvidersView
    {
        $providerId = ProviderId::fromString($query->getProviderId());
        $userId = UserId::fromString($query->getUserId());

        if (!$this->providerUserRepository->isAdmin($providerId, $userId)) {
            throw ProviderAdminRoleRequired::create();
        }

        return $this->providerReadRepository->findAvailableProvidersToLink($providerId, $userId);
    }
}
