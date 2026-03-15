<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\RequestUserEmailChange;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\EmailVerificationSlugGenerator;
use App\Shared\Application\Event\DomainEventDispatcher;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;

final readonly class RequestUserEmailChangeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailVerificationSlugGenerator $emailVerificationSlugGenerator,
        private Clock $clock,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function __invoke(RequestUserEmailChange $command): void
    {
        $userId = UserId::fromString($command->getUserId());
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return;
        }

        $existingByEmail = $this->userRepository->findByEmail($command->getNewEmail());

        if ($existingByEmail !== null && !$existingByEmail->getId()->equals($user->getId())) {
            return;
        }

        $existingByPendingEmail = $this->userRepository->findByPendingEmail($command->getNewEmail());

        if ($existingByPendingEmail !== null && !$existingByPendingEmail->getId()->equals($user->getId())) {
            return;
        }

        $slug = $this->emailVerificationSlugGenerator->generate();
        $user->requestEmailChange($command->getNewEmail(), $slug, $this->clock->now());
        $this->userRepository->save($user);
        $this->domainEventDispatcher->dispatchAll($user->pullDomainEvents());
    }
}
