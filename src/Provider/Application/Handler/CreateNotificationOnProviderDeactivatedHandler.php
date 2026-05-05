<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderDeactivated;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Domain\Id\ProviderId;

final readonly class CreateNotificationOnProviderDeactivatedHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ProviderUserRepository $providerUserRepository,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(ProviderDeactivated $event): void
    {
        $providerId = ProviderId::fromString($event->getProviderId());
        $provider = $this->providerRepository->findById($providerId);

        if ($provider === null) {
            return;
        }

        $adminUserIds = $this->providerUserRepository->findUserIdsByProviderIdAndRole(
            $providerId,
            ProviderUserRole::Admin,
        );

        foreach ($adminUserIds as $adminUserId) {
            $this->notificationSender->send(
                $adminUserId,
                'provider_deactivated',
                'Your provider has been deactivated',
                sprintf('Your provider "%s" has been deactivated.', $provider->getName()),
                [
                    'providerId' => $providerId->toString(),
                    'url' => $this->frontendUrlCreator->provider($providerId->toString()),
                ],
            );
        }
    }
}
