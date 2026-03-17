<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
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
            $providerUser->getRole()->value,
        );

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function isMember(ProviderId $providerId, UserId $userId): bool
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'userId' => $userId->toString(),
            ]);

        return $record instanceof ProviderUserRecord;
    }

    public function isAdmin(ProviderId $providerId, UserId $userId): bool
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'userId' => $userId->toString(),
                'role' => ProviderUserRole::Admin->value,
            ]);

        return $record instanceof ProviderUserRecord;
    }

    public function findUserIdsByProviderIdAndRole(ProviderId $providerId, ProviderUserRole $role): array
    {
        $records = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findBy([
                'providerId' => $providerId->toString(),
                'role' => $role->value,
            ]);

        $userIds = [];

        foreach ($records as $record) {
            $userIds[] = UserId::fromString($record->getUserId());
        }

        return $userIds;
    }
}
