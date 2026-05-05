<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Provider\Infrastructure\Doctrine\Mapper\ProviderUserRecordMapper;
use App\Shared\Application\ProviderUser\ProviderAdminFinder;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderUserRepository implements ProviderUserRepository, ProviderAdminFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProviderUserRecordMapper $providerUserRecordMapper,
    ) {
    }

    public function save(ProviderUser $providerUser): void
    {
        $existing = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->find($providerUser->getId()->toString());

        if ($existing instanceof ProviderUserRecord) {
            $this->providerUserRecordMapper->syncRecord($providerUser, $existing);

            return;
        }

        $this->entityManager->persist($this->providerUserRecordMapper->toRecord($providerUser));
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

    public function isActiveMember(ProviderId $providerId, UserId $userId): bool
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'userId' => $userId->toString(),
                'status' => ProviderUserStatus::Active->value,
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

    public function findById(ProviderUserId $providerUserId): ?ProviderUser
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->find($providerUserId->toString());

        if (!$record instanceof ProviderUserRecord) {
            return null;
        }

        return $this->providerUserRecordMapper->toDomain($record);
    }

    public function findByProviderIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderUser
    {
        $record = $this->entityManager
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'userId' => $userId->toString(),
            ]);

        if (!$record instanceof ProviderUserRecord) {
            return null;
        }

        return $this->providerUserRecordMapper->toDomain($record);
    }

    public function findAdminUserIdsByProviderId(ProviderId $providerId): array
    {
        return $this->findUserIdsByProviderIdAndRole($providerId, ProviderUserRole::Admin);
    }
}
