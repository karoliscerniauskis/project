<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\InviteProviderUser;
use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Slug\ProviderInvitationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class InviteProviderUserHandler
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
        private ProviderInvitationRepository $providerInvitationRepository,
        private UuidCreator $uuidCreator,
        private Clock $clock,
        private ProviderInvitationSlugGenerator $providerInvitationSlugGenerator,
        private TransactionManager $transactionManager,
        private OutboxWriter $outboxWriter,
    ) {
    }

    public function __invoke(InviteProviderUser $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $providerId = ProviderId::fromString($command->getProviderId());
            $invitedByUserId = UserId::fromString($command->getInvitedByUserId());

            if (!$this->providerUserRepository->isAdmin($providerId, $invitedByUserId)) {
                throw ProviderAccessDenied::create();
            }

            if ($this->providerInvitationRepository->existsAcceptedByProviderIdAndEmail($providerId, $command->getEmail())) {
                return;
            }

            $existingPending = $this->providerInvitationRepository->findPendingByProviderIdAndEmail($providerId, $command->getEmail());

            if ($existingPending !== null) {
                return;
            }

            $invitation = ProviderInvitation::invite(
                ProviderInvitationId::fromString($this->uuidCreator->create()),
                $providerId,
                $command->getEmail(),
                ProviderUserRole::Member,
                $this->providerInvitationSlugGenerator->generate(),
                $invitedByUserId,
                $this->clock->now(),
                $this->clock->now()->modify('+7 days'),
            );
            $this->providerInvitationRepository->save($invitation);
            $this->outboxWriter->storeAll($invitation->pullDomainEvents());
        });
    }
}
