<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\RequestPasswordReset;
use App\Auth\Domain\Repository\UserRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use DateTimeImmutable;

final readonly class RequestPasswordResetHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(RequestPasswordReset $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $user = $this->userRepository->findByEmail($command->getEmail());

            if ($user === null) {
                return;
            }

            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
            $occurredOn = new DateTimeImmutable();

            $user->requestPasswordReset($resetToken, $expiresAt, $occurredOn);
            $this->userRepository->save($user);
            $this->outboxWriter->storeAll($user->pullDomainEvents());
        });
    }
}
