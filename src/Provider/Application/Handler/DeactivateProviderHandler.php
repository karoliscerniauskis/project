<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\DeactivateProvider;
use App\Provider\Application\Exception\AdminRoleRequired;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Security\AdminRoleChecker;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class DeactivateProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private AdminRoleChecker $adminRoleChecker,
        private TransactionManager $transactionManager,
        private Clock $clock,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(DeactivateProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            if (!$this->adminRoleChecker->isAdmin(UserId::fromString($command->getUserId()))) {
                throw AdminRoleRequired::create();
            }

            $provider = $this->providerRepository->findById(
                ProviderId::fromString($command->getProviderId()),
            );

            if ($provider === null) {
                return;
            }

            $provider->deactivate($this->clock->now());
            $this->providerRepository->save($provider);
            $this->outboxWriter->storeAll($provider->pullDomainEvents());
        });
    }
}
