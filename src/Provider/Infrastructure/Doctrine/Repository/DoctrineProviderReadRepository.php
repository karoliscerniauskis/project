<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Provider\Domain\View\PaginatedProvidersView;
use App\Provider\Domain\View\ProvidersView;
use App\Provider\Domain\View\ProviderView;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineProviderReadRepository implements ProviderReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUserId(UserId $userId): ProvidersView
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status', 'pu.role AS role')
            ->from(ProviderRecord::class, 'p')
            ->innerJoin(
                ProviderUserRecord::class,
                'pu',
                'WITH',
                'pu.providerId = p.id',
            )
            ->andWhere('pu.userId = :userId')
            ->andWhere('pu.status = :userStatus')
            ->setParameter('userId', $userId->toString())
            ->setParameter('userStatus', ProviderUserStatus::Active->value)
            ->getQuery()
            ->getArrayResult();
        $providers = [];

        /** @var array{id: Uuid, name: string, status: string, role: string} $row */
        foreach ($rows as $row) {
            $providers[] = new ProviderView(
                $row['id']->toRfc4122(),
                $row['name'],
                $row['status'],
                $row['role'] === ProviderUserRole::Admin->value,
            );
        }

        return new ProvidersView($providers);
    }

    public function findByUserIdPaginated(
        UserId $userId,
        int $limit,
        int $offset,
        ?string $nameFilter = null,
    ): PaginatedProvidersView {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status', 'pu.role AS role')
            ->from(ProviderRecord::class, 'p')
            ->innerJoin(
                ProviderUserRecord::class,
                'pu',
                'WITH',
                'pu.providerId = p.id',
            )
            ->andWhere('pu.userId = :userId')
            ->andWhere('pu.status = :userStatus')
            ->setParameter('userId', $userId->toString())
            ->setParameter('userStatus', ProviderUserStatus::Active->value);

        if ($nameFilter !== null && $nameFilter !== '') {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:nameFilter)')
                ->setParameter('nameFilter', '%'.$nameFilter.'%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(DISTINCT p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $rows = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $providers = [];

        /** @var array{id: Uuid, name: string, status: string, role: string} $row */
        foreach ($rows as $row) {
            $providers[] = new ProviderView(
                $row['id']->toRfc4122(),
                $row['name'],
                $row['status'],
                $row['role'] === ProviderUserRole::Admin->value,
            );
        }

        return new PaginatedProvidersView(
            $providers,
            (int) ceil(($offset + 1) / $limit),
            $limit,
            $total,
        );
    }

    public function findActiveByIdAndUserId(ProviderId $providerId, UserId $userId): ?ProviderView
    {
        /** @var array{id: Uuid, name: string, status: string, role: string}|null $row */
        $row = $this->entityManager->createQueryBuilder()
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status', 'pu.role AS role')
            ->from(ProviderRecord::class, 'p')
            ->innerJoin(
                ProviderUserRecord::class,
                'pu',
                'WITH',
                'pu.providerId = p.id',
            )
            ->andWhere('p.id = :providerId')
            ->andWhere('pu.userId = :userId')
            ->andWhere('pu.status = :userStatus')
            ->setParameter('providerId', $providerId->toString())
            ->setParameter('userId', $userId->toString())
            ->setParameter('userStatus', ProviderUserStatus::Active->value)
            ->getQuery()
            ->getOneOrNullResult();

        if ($row === null) {
            return null;
        }

        return new ProviderView(
            $row['id']->toRfc4122(),
            $row['name'],
            $row['status'],
            $row['role'] === ProviderUserRole::Admin->value,
        );
    }

    public function findAllForAdmin(): ProvidersView
    {
        /** @var array<int, array{id: Uuid, name: string, status: string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status')
            ->from(ProviderRecord::class, 'p')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $providers = [];

        foreach ($rows as $row) {
            $providers[] = new ProviderView(
                $row['id']->toRfc4122(),
                $row['name'],
                $row['status'],
                true,
            );
        }

        return new ProvidersView($providers);
    }

    public function findAllForAdminPaginated(
        int $limit,
        int $offset,
        ?string $nameFilter = null,
        ?string $statusFilter = null,
    ): PaginatedProvidersView {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status')
            ->from(ProviderRecord::class, 'p');

        if ($nameFilter !== null && $nameFilter !== '') {
            $qb->andWhere('LOWER(p.name) LIKE LOWER(:nameFilter)')
                ->setParameter('nameFilter', '%'.$nameFilter.'%');
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $qb->andWhere('p.status = :statusFilter')
                ->setParameter('statusFilter', $statusFilter);
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /** @var array<int, array{id: Uuid, name: string, status: string}> $rows */
        $rows = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $providers = [];

        foreach ($rows as $row) {
            $providers[] = new ProviderView(
                $row['id']->toRfc4122(),
                $row['name'],
                $row['status'],
                true,
            );
        }

        return new PaginatedProvidersView(
            $providers,
            (int) ceil(($offset + 1) / $limit),
            $limit,
            $total,
        );
    }
}
