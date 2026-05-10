<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\ResetPassword;
use App\Auth\Application\Exception\InvalidPasswordResetToken;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;

final readonly class ResetPasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasher $userPasswordHasher,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
        private Clock $clock,
    ) {
    }

    public function __invoke(ResetPassword $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $resetToken = $command->getResetToken();
            $user = $this->userRepository->findByResetToken($resetToken);

            if ($user === null || !$user->isPasswordResetTokenValid($resetToken, $this->clock->now())) {
                throw InvalidPasswordResetToken::create();
            }

            $hashedNewPassword = $this->userPasswordHasher->hashPassword($command->getNewPassword());
            $user->resetPassword($hashedNewPassword);
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
