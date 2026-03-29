<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\ChangeUserPassword;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\UserId;

final readonly class ChangeUserPasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasher $userPasswordHasher,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(ChangeUserPassword $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $userId = UserId::fromString($command->getUserId());
            $user = $this->userRepository->findById($userId);

            if ($user === null || !$this->userPasswordHasher->isPasswordValid($command->getCurrentPassword(), $user->getHashedPassword())) {
                return;
            }

            $hashedNewPassword = $this->userPasswordHasher->hashPassword($command->getNewPassword());
            $user->changePassword($hashedNewPassword);
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
