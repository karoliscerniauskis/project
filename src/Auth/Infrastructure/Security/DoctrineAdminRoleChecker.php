<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Application\Security\AdminRoleChecker;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAdminRoleChecker implements AdminRoleChecker
{
    private const string ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function isAdmin(UserId $userId): bool
    {
        $user = $this->entityManager
            ->getRepository(UserRecord::class)
            ->find($userId->toString());

        if (!$user instanceof UserRecord) {
            return false;
        }

        return in_array(self::ROLE_ADMIN, $user->getRoles(), true);
    }
}
