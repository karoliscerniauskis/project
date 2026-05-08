<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Shared\Application\Provider\ProviderReminderSettings;
use App\Shared\Application\Provider\ProviderReminderSettingsFinder;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderReminderSettingsFinder implements ProviderReminderSettingsFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByProviderId(ProviderId $providerId): ?ProviderReminderSettings
    {
        $record = $this->entityManager->getRepository(ProviderRecord::class)->find($providerId->toString());

        if (!$record instanceof ProviderRecord) {
            return null;
        }

        return new ProviderReminderSettings(
            $record->getClaimReminderAfterDays(),
            $record->getExpiryReminderBeforeDays(),
        );
    }
}
