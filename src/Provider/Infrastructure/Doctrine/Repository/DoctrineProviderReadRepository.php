<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Repository\ProviderReadRepository;
use App\Provider\Domain\View\ProvidersView;
use App\Provider\Domain\View\ProviderView;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
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
            ->select('p.id AS id', 'p.name AS name', 'p.status AS status')
            ->from(ProviderRecord::class, 'p')
            ->innerJoin(
                ProviderUserRecord::class,
                'pu',
                'WITH',
                'pu.providerId = p.id',
            )
            ->andWhere('pu.userId = :userId')
            ->setParameter('userId', $userId->toString())
            ->getQuery()
            ->getArrayResult();
        $providers = [];

        /** @var array{id: Uuid, name: string, status: string} $row */
        foreach ($rows as $row) {
            $providers[] = new ProviderView(
                $row['id']->toRfc4122(),
                $row['name'],
                $row['status'],
            );
        }

        return new ProvidersView($providers);
    }
}
