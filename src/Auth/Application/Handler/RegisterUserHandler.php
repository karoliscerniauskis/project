<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\RegisterUser;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Application\Event\DomainEventDispatcher;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UuidCreator $uuidCreator,
        private Clock $clock,
        private UserPasswordHasher $passwordHasher,
        private EmailVerificationSlugGenerator $emailVerificationSlugGenerator,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function __invoke(RegisterUser $command): void
    {
        $user = User::register(
            UserId::fromString($this->uuidCreator->create()),
            $command->getEmail(),
            $this->passwordHasher->hashPassword($command->getPassword()),
            $command->getRoles(),
            $this->emailVerificationSlugGenerator->generate(),
            $this->clock->now(),
        );

        $this->userRepository->save($user);
        $this->domainEventDispatcher->dispatchAll($user->pullDomainEvents());
    }
}
