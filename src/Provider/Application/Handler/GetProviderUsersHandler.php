<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Application\Query\GetProviderUsers;
use App\Provider\Domain\Repository\ProviderUserReadRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\View\ProviderUsersView;

final readonly class GetProviderUsersHandler
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
        private ProviderUserReadRepository $providerUserReadRepository,
    ) {
    }

    public function __invoke(GetProviderUsers $query): ProviderUsersView
    {
        if (!$this->providerUserRepository->isActiveMember($query->getProviderId(), $query->getUserId())) {
            throw ProviderAccessDenied::create();
        }

        return $this->providerUserReadRepository->findActiveMembersByProviderId($query->getProviderId());
    }
}
