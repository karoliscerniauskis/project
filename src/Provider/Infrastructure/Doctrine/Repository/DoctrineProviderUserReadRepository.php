<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Repository\ProviderUserReadRepository;
use App\Provider\Domain\View\ProviderUsersView;
use App\Provider\Domain\View\ProviderUserView;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderUserReadRepository implements ProviderUserReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByProviderId(ProviderId $providerId): ProviderUsersView
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('pu.userId AS userId', 'pu.role AS role', 'pu.status AS status', 'u.email AS email')
            ->from(ProviderUserRecord::class, 'pu')
            ->innerJoin(
                'App\\Auth\\Infrastructure\\Doctrine\\Entity\\UserRecord',
                'u',
                'WITH',
                'u.id = pu.userId',
            )
            ->andWhere('pu.providerId = :providerId')
            ->setParameter('providerId', $providerId->toString())
            ->getQuery()
            ->getArrayResult();

        $users = [];

        foreach ($rows as $row) {
            /** @var array{email: mixed, role: mixed, status: mixed} $row */
            if (!is_string($row['email']) || !is_string($row['role']) || !is_string($row['status'])) {
                continue;
            }

            $users[] = new ProviderUserView(
                $row['email'],
                $row['role'],
                $row['status'],
            );
        }

        return new ProviderUsersView($users);
    }
}
