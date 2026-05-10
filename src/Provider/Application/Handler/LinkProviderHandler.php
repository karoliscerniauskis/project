<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\LinkProvider;
use App\Provider\Application\Exception\ProviderAdminRoleRequired;
use App\Provider\Application\Exception\ProviderAlreadyLinked;
use App\Provider\Domain\Entity\ProviderLink;
use App\Provider\Domain\Repository\ProviderLinkRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderLinkId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class LinkProviderHandler
{
    public function __construct(
        private ProviderLinkRepository $providerLinkRepository,
        private ProviderUserRepository $providerUserRepository,
        private UuidCreator $uuidCreator,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(LinkProvider $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $providerId = ProviderId::fromString($command->getProviderId());
            $linkedProviderId = ProviderId::fromString($command->getLinkedProviderId());
            $userId = UserId::fromString($command->getUserId());

            if (!$this->providerUserRepository->isAdmin($providerId, $userId)) {
                throw ProviderAdminRoleRequired::create();
            }

            if (!$this->providerUserRepository->isAdmin($linkedProviderId, $userId)) {
                throw ProviderAdminRoleRequired::create();
            }

            $existingLink = $this->providerLinkRepository->findByProviderAndLinkedProvider(
                $providerId,
                $linkedProviderId
            );

            if ($existingLink !== null) {
                throw ProviderAlreadyLinked::create();
            }

            $providerLink = ProviderLink::create(
                ProviderLinkId::fromString($this->uuidCreator->create()),
                $providerId,
                $linkedProviderId,
            );

            $this->providerLinkRepository->save($providerLink);
        });
    }
}
