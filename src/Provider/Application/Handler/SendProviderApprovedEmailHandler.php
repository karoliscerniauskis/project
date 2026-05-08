<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\User\UserEmailFinder;
use App\Shared\Domain\Id\ProviderId;

final readonly class SendProviderApprovedEmailHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ProviderUserRepository $providerUserRepository,
        private UserEmailFinder $userEmailFinder,
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(ProviderApproved $event): void
    {
        $providerId = ProviderId::fromString($event->getProviderId());
        $provider = $this->providerRepository->findById($providerId);

        if ($provider === null || !$provider->isActive()) {
            return;
        }

        $providerName = $provider->getName();
        $providerUrl = $this->frontendUrlCreator->provider($providerId->toString());
        $adminUserIds = $this->providerUserRepository->findUserIdsByProviderIdAndRole(
            $providerId,
            ProviderUserRole::Admin,
        );

        foreach ($adminUserIds as $adminUserId) {
            $email = $this->userEmailFinder->findByUserId($adminUserId);

            if ($email === null) {
                continue;
            }

            $this->emailSender->send(
                $this->emailFrom,
                $email,
                'Your provider has been approved',
                sprintf(
                    'Your provider "%s" has been approved and is now active. Click the button below to view it.',
                    $providerName,
                ),
                $providerUrl,
                'View provider',
            );
        }
    }
}
