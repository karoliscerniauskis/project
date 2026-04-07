<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\CancelProviderInvitation;
use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\Transaction\TransactionManager;

final readonly class CancelProviderInvitationHandler
{
    public function __construct(
        private ProviderUserRepository $providerUserRepository,
        private ProviderInvitationRepository $providerInvitationRepository,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(CancelProviderInvitation $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            if (!$this->providerUserRepository->isAdmin($command->getProviderId(), $command->getUserId())) {
                throw ProviderAccessDenied::create();
            }

            $invitation = $this->providerInvitationRepository->findPendingByProviderIdAndEmail(
                $command->getProviderId(),
                $command->getEmail(),
            );

            if ($invitation === null) {
                return;
            }

            if ($invitation->getStatus() !== ProviderInvitationStatus::Pending) {
                return;
            }

            $invitation->cancel();
            $this->providerInvitationRepository->save($invitation);
        });
    }
}
