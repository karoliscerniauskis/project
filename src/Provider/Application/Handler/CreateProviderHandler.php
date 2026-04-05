<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\CreateProvider;
use App\Provider\Application\Exception\ProviderNameAlreadyExists;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class CreateProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ProviderUserRepository $providerUserRepository,
        private UuidCreator $uuidCreator,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(CreateProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            if ($this->providerRepository->existsByName($command->getName())) {
                throw ProviderNameAlreadyExists::forName($command->getName());
            }

            $userId = UserId::fromString($command->getUserId());

            $provider = Provider::create(
                ProviderId::fromString($this->uuidCreator->create()),
                $command->getName(),
                ProviderStatus::Pending,
            );

            $providerUser = ProviderUser::assignAdmin(
                ProviderUserId::fromString($this->uuidCreator->create()),
                $provider->getId(),
                $userId,
            );

            $this->providerRepository->save($provider);
            $this->outboxWriter->storeAll($provider->pullDomainEvents());
            $this->providerUserRepository->save($providerUser);
            $this->outboxWriter->storeAll($providerUser->pullDomainEvents());
        });
    }
}
