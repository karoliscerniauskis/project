<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Domain\Event\ProviderUserRemoved;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class CreateNotificationOnProviderUserRemovedHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private NotificationSender $notificationSender,
    ) {
    }

    public function __invoke(ProviderUserRemoved $event): void
    {
        $providerId = ProviderId::fromString($event->getProviderId());
        $provider = $this->providerRepository->findById($providerId);

        $this->notificationSender->send(
            UserId::fromString($event->getUserId()),
            'provider_user_removed',
            'You have been removed from a provider',
            sprintf(
                'You have been removed from provider "%s".',
                $provider?->getName() ?? 'Unknown provider',
            ),
            [
                'providerId' => $event->getProviderId(),
            ],
        );
    }
}
