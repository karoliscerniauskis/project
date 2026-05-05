<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;

final readonly class CreateNotificationOnProviderInvitationCreatedHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(ProviderInvitationCreated $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getEmail());

        if ($userId === null) {
            return;
        }

        $this->notificationSender->send(
            $userId,
            'provider_invitation_created',
            'You are invited to join a provider',
            'You have received an invitation to join a provider.',
            [
                'providerInvitationId' => $event->getProviderInvitationId(),
                'url' => $this->frontendUrlCreator->acceptProviderInvitation($event->getSlug()),
            ],
        );
    }
}
