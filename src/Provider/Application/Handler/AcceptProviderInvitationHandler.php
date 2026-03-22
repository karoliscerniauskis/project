<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\AcceptProviderInvitation;
use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Application\User\UserEmailFinder;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class AcceptProviderInvitationHandler
{
    public function __construct(
        private ProviderInvitationRepository $providerInvitationRepository,
        private ProviderRepository $providerRepository,
        private ProviderUserRepository $providerUserRepository,
        private UserEmailFinder $userEmailFinder,
        private Clock $clock,
        private UuidCreator $uuidCreator,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(AcceptProviderInvitation $command): void
    {
        $invitation = $this->providerInvitationRepository->findBySlug($command->getSlug());

        if ($invitation === null) {
            return;
        }

        $provider = $this->providerRepository->findById($invitation->getProviderId());

        if ($provider === null) {
            return;
        }

        $userId = UserId::fromString($command->getUserId());
        $userEmail = $this->userEmailFinder->findByUserId($userId);

        if ($userEmail === null) {
            return;
        }

        if ($invitation->getEmail() !== $userEmail) {
            return;
        }

        $invitation->accept($userId, $this->clock->now());
        $this->providerInvitationRepository->save($invitation);
        $providerUser = ProviderUser::assignMember(
            ProviderUserId::fromString($this->uuidCreator->create()),
            $invitation->getProviderId(),
            $userId,
        );
        $this->providerUserRepository->save($providerUser);
        $this->transactionManager->flush();
    }
}
