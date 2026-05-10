<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Exception\AdminRoleRequired;
use App\Provider\Application\Query\GetAdminProviders;
use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\View\PaginatedProvidersView;
use App\Shared\Application\Security\AdminRoleChecker;
use App\Shared\Domain\Id\UserId;

final readonly class GetAdminProvidersHandler
{
    public function __construct(
        private ProviderReadRepository $providerReadRepository,
        private AdminRoleChecker $adminRoleChecker,
    ) {
    }

    public function __invoke(GetAdminProviders $query): PaginatedProvidersView
    {
        if (!$this->adminRoleChecker->isAdmin(UserId::fromString($query->getUserId()))) {
            throw AdminRoleRequired::create();
        }

        return $this->providerReadRepository->findAllForAdminPaginated(
            $query->getLimit(),
            $query->getOffset(),
            $query->getNameFilter(),
            $query->getStatusFilter(),
        );
    }
}
