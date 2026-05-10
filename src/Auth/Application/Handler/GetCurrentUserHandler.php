<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Query\GetCurrentUser;
use App\Auth\Domain\Repository\UserReadRepository;
use App\Auth\Domain\View\UserProfileView;

final readonly class GetCurrentUserHandler
{
    public function __construct(
        private UserReadRepository $userReadRepository,
    ) {
    }

    public function __invoke(GetCurrentUser $query): ?UserProfileView
    {
        return $this->userReadRepository->getUserProfile($query->getUserId());
    }
}
