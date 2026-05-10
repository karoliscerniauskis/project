<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\ConfigureEmailBreachCheckSettings;
use App\Auth\Application\Exception\UserNotFound;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\UserId;

final readonly class ConfigureEmailBreachCheckSettingsHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(ConfigureEmailBreachCheckSettings $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $userId = UserId::fromString($command->getUserId());
            $user = $this->userRepository->findById($userId);

            if (!$user instanceof User) {
                throw UserNotFound::forId($userId->toString());
            }

            $user->configureEmailBreachCheck($command->isEnabled());
            $this->userRepository->save($user);
        });
    }
}
