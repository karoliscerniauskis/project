<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\UnlinkProvider;
use App\Provider\Application\Exception\ProviderAdminRoleRequired;
use App\Provider\Application\Exception\ProviderNotLinked;
use App\Provider\Domain\Repository\ProviderLinkRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class UnlinkProviderHandler
{
    public function __construct(
        private ProviderLinkRepository $providerLinkRepository,
        private ProviderUserRepository $providerUserRepository,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(UnlinkProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $providerId = ProviderId::fromString($command->getProviderId());
            $linkedProviderId = ProviderId::fromString($command->getLinkedProviderId());
            $userId = UserId::fromString($command->getUserId());

            if (!$this->providerUserRepository->isAdmin($providerId, $userId)) {
                throw ProviderAdminRoleRequired::create();
            }

            $providerLink = $this->providerLinkRepository->findByProviderAndLinkedProvider(
                $providerId,
                $linkedProviderId
            );

            if ($providerLink === null) {
                throw ProviderNotLinked::create();
            }

            $this->providerLinkRepository->delete($providerLink);
        });
    }
}
