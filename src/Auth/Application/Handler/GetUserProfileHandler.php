<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Exception\UserNotFound;
use App\Auth\Application\Query\GetUserProfile;
use App\Auth\Domain\Repository\UserReadRepository;
use App\Auth\Domain\View\UserProfileView;
use App\Shared\Domain\Id\UserId;

final readonly class GetUserProfileHandler
{
    public function __construct(
        private UserReadRepository $userReadRepository,
    ) {
    }

    public function __invoke(GetUserProfile $query): UserProfileView
    {
        $userProfile = $this->userReadRepository->getUserProfile(UserId::fromString($query->userId));

        if ($userProfile === null) {
            throw UserNotFound::forId($query->userId);
        }

        return $userProfile;
    }
}
