<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
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
            $providerUser->getId(),
            $providerUser->getProviderId(),
            $providerUser->getUserId(),
        );

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findProviderIdByUserId(string $userId): ?string
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy(['userId' => $userId]);

        if (!$record instanceof ProviderUserRecord) {
            return null;
        }

        return $record->getProviderId();
    }
}
