<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\User;

use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Application\User\UserEmailFinder;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserEmailFinder implements UserEmailFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByUserId(UserId $userId): ?string
    {
        $userRecord = $this->entityManager
            ->getRepository(UserRecord::class)
            ->find($userId->toString());

        if (!$userRecord instanceof UserRecord) {
            return null;
        }

        return $userRecord->getEmail();
    }
}
