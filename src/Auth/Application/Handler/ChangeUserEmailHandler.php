<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\ChangeUserEmail;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\EmailVerificationSlugGenerator;
use App\Shared\Application\Event\DomainEventDispatcher;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;

final readonly class ChangeUserEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailVerificationSlugGenerator $emailVerificationSlugGenerator,
        private Clock $clock,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function __invoke(ChangeUserEmail $command): void
    {
        $userId = UserId::fromString($command->getUserId());
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return;
        }

        $slug = $this->emailVerificationSlugGenerator->generate();
        $user->changeEmail($command->getNewEmail(), $slug, $this->clock->now());
        $this->userRepository->save($user);
        $this->domainEventDispatcher->dispatchAll($user->pullDomainEvents());
    }
}
