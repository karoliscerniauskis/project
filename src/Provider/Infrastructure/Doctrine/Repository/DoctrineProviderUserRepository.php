<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderUserRepository implements ProviderUserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ProviderUser $providerUser): void
    {
        $record = new ProviderUserRecord(
            $providerUser->getId()->toString(),
            $providerUser->getProviderId()->toString(),
            $providerUser->getUserId()->toString(),
        );

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findProviderIdByUserId(UserId $userId): ?ProviderId
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy(['userId' => $userId->toString()]);

        if (!$record instanceof ProviderUserRecord) {
            return null;
        }

        return ProviderId::fromString($record->getProviderId());
    }
}
