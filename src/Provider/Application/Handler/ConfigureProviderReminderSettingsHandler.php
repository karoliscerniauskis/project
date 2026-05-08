<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\ConfigureProviderReminderSettings;
use App\Provider\Application\Exception\ProviderAdminRoleRequired;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Shared\Application\Security\ProviderAccessChecker;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;

final readonly class ConfigureProviderReminderSettingsHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ProviderAccessChecker $providerAccessChecker,
        private TransactionManager $transactionManager,
    ) {
    }

    public function __invoke(ConfigureProviderReminderSettings $command): void
    {
        $this->transactionManager->transactional(function () use ($command): void {
            $providerId = ProviderId::fromString($command->getProviderId());
            $userId = UserId::fromString($command->getUserId());

            if (!$this->providerAccessChecker->isAdmin($providerId, $userId)) {
                throw ProviderAdminRoleRequired::create();
            }

            $provider = $this->providerRepository->findById($providerId);

            if ($provider === null) {
                return;
            }

            $provider->configureReminderSettings(
                $command->getClaimReminderAfterDays(),
                $command->getExpiryReminderBeforeDays(),
            );

            $this->providerRepository->save($provider);
        });
    }
}
