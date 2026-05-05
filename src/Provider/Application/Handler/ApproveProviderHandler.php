<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\ApproveProvider;
use App\Provider\Application\Exception\AdminRoleRequired;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Security\AdminRoleChecker;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class ApproveProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private Clock $clock,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
        private AdminRoleChecker $adminRoleChecker,
    ) {
    }

    public function __invoke(ApproveProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            if (!$this->adminRoleChecker->isAdmin(UserId::fromString($command->getUserId()))) {
                throw AdminRoleRequired::create();
            }

            $providerId = ProviderId::fromString($command->getProviderId());
            $provider = $this->providerRepository->findById($providerId);

            if ($provider === null) {
                return;
            }

            $provider->approve($this->clock->now());
            $this->providerRepository->save($provider);
            $this->outboxWriter->storeAll($provider->pullDomainEvents());
        });
    }
}
