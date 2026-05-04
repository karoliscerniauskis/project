<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\RemoveProviderUser;
use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\Transaction\TransactionManager;

final readonly class RemoveProviderUserHandler
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(RemoveProviderUser $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            if (!$this->providerUserRepository->isAdmin($command->getProviderId(), $command->getRequestedByUserId())) {
                throw ProviderAccessDenied::create();
            }

            $providerUser = $this->providerUserRepository->findById($command->getProviderUserId());

            if (
                $providerUser === null
                || $providerUser->isAdmin()
                || $providerUser->getProviderId()->toString() !== $command->getProviderId()->toString()
            ) {
                return;
            }

            $providerUser->remove();
            $this->providerUserRepository->save($providerUser);
        });
    }
}
