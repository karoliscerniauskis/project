<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\RequestUserEmailChange;
use App\Auth\Application\Exception\UserEmailMustBeUnique;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;

final readonly class RequestUserEmailChangeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailVerificationSlugGenerator $emailVerificationSlugGenerator,
        private Clock $clock,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(RequestUserEmailChange $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $userId = UserId::fromString($command->getUserId());
            $user = $this->userRepository->findById($userId);

            if ($user === null) {
                return;
            }

            $existingByEmail = $this->userRepository->findByEmail($command->getNewEmail());

            if ($existingByEmail !== null && !$existingByEmail->getId()->equals($user->getId())) {
                throw UserEmailMustBeUnique::forEmail($command->getNewEmail());
            }

            $existingByPendingEmail = $this->userRepository->findByPendingEmail($command->getNewEmail());

            if ($existingByPendingEmail !== null && !$existingByPendingEmail->getId()->equals($user->getId())) {
                throw UserEmailMustBeUnique::forEmail($command->getNewEmail());
            }

            $slug = $this->emailVerificationSlugGenerator->generate();
            $user->requestEmailChange($command->getNewEmail(), $slug, $this->clock->now());
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
