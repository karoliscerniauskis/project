<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Application\Query\GetProviderInvitations;
use App\Provider\Domain\Repository\ProviderInvitationReadRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\View\ProviderInvitationsView;

final readonly class GetProviderInvitationsHandler
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
        private ProviderInvitationReadRepository $providerInvitationReadRepository,
    ) {
    }

    public function __invoke(GetProviderInvitations $query): ProviderInvitationsView
    {
        if (!$this->providerUserRepository->isActiveMember($query->getProviderId(), $query->getUserId())) {
            throw ProviderAccessDenied::create();
        }

        return $this->providerInvitationReadRepository->findPendingByProviderId($query->getProviderId());
    }
}
