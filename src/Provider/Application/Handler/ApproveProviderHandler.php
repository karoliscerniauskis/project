<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\ApproveProvider;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Domain\Id\ProviderId;

final readonly class ApproveProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
    ) {
    }

    public function __invoke(ApproveProvider $command): void
    {
        $providerId = ProviderId::fromString($command->getProviderId());
        $provider = $this->providerRepository->findById($providerId);

        if ($provider === null) {
            return;
        }

        $provider->approve();
        $this->providerRepository->save($provider);
    }
}
