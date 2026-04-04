<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\RegisterUser;
use App\Auth\Application\Exception\UserEmailMustBeUnique;
use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Domain\Security\UserPasswordHasher;
use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
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
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(RegisterUser $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $email = $command->getEmail();

            if ($this->userRepository->findByEmail($email) instanceof User) {
                throw UserEmailMustBeUnique::forEmail($email);
            }

            $user = User::register(
                UserId::fromString($this->uuidCreator->create()),
                $email,
                $this->passwordHasher->hashPassword($command->getPassword()),
                $command->getRoles(),
                $this->emailVerificationSlugGenerator->generate(),
                $this->clock->now(),
            );
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
