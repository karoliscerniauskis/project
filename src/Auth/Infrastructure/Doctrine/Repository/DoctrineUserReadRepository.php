<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Repository;

use App\Auth\Domain\Repository\UserReadRepository;
use App\Auth\Domain\View\UserProfileView;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserReadRepository implements UserReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getUserProfile(UserId $userId): ?UserProfileView
    {
        $qb = $this->entityManager->createQueryBuilder();

        /** @var array{email: string, emailBreachCheckEnabled: bool, roles: array<string>}|null $result */
        $result = $qb
            ->select('u.email', 'u.emailBreachCheckEnabled', 'u.roles')
            ->from(UserRecord::class, 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId->toString())
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            return null;
        }

        return new UserProfileView(
            email: $result['email'],
            emailBreachCheckEnabled: $result['emailBreachCheckEnabled'],
            roles: $result['roles'],
        );
    }
}
