<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\VerifyUserEmail;
use App\Auth\Domain\Repository\UserRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;

final readonly class VerifyUserEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private Clock $clock,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(VerifyUserEmail $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $user = $this->userRepository->findByEmailVerificationSlug($command->getEmailVerificationSlug());

            if ($user === null) {
                return;
            }

            $user->verifyEmail($this->clock->now());
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
