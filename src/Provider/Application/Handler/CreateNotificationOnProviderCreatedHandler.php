<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderCreated;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\Security\AdminUserFinder;

final readonly class CreateNotificationOnProviderCreatedHandler
{
    public function __construct(
        private AdminUserFinder $adminUserFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(ProviderCreated $event): void
    {
        foreach ($this->adminUserFinder->findAdminUserIds() as $adminUserId) {
            $this->notificationSender->send(
                $adminUserId,
                'provider_created',
                'Provider approval required',
                sprintf('Provider "%s" has been created and needs approval.', $event->getProviderName()),
                [
                    'providerId' => $event->getProviderId(),
                    'url' => $this->frontendUrlCreator->adminProviders(),
                ],
            );
        }
    }
}
