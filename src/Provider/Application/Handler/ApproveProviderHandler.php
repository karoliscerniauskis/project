<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\ApproveProvider;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;

final readonly class ApproveProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private Clock $clock,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(ApproveProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
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
