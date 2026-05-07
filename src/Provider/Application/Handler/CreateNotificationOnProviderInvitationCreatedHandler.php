<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;
use App\Shared\Domain\Id\ProviderId;

final readonly class CreateNotificationOnProviderInvitationCreatedHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private ProviderRepository $providerRepository,
    ) {
    }

    public function __invoke(ProviderInvitationCreated $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getEmail());

        if ($userId === null) {
            return;
        }

        $provider = $this->providerRepository->findById(
            ProviderId::fromString($event->getProviderId()),
        );
        $providerName = $provider?->getName() ?? 'Unknown provider';

        $this->notificationSender->send(
            $userId,
            'provider_invitation_created',
            'You are invited to join a provider',
            sprintf('You have received an invitation to join provider "%s".', $providerName),
            [
                'providerInvitationId' => $event->getProviderInvitationId(),
                'providerId' => $event->getProviderId(),
                'providerName' => $providerName,
                'url' => $this->frontendUrlCreator->acceptProviderInvitation($event->getSlug()),
            ],
        );
    }
}
