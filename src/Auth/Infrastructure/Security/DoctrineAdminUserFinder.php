<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Shared\Application\Security\AdminUserFinder;
use App\Shared\Domain\Id\UserId;
use Doctrine\DBAL\Connection;

final readonly class DoctrineAdminUserFinder implements AdminUserFinder
{
    private const string ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return UserId[]
     */
    public function findAdminUserIds(): array
    {
        /** @var string[] $userIds */
        $userIds = $this->connection
            ->createQueryBuilder()
            ->select('id')
            ->from('auth_user')
            ->where('jsonb_exists(roles::jsonb, :role)')
            ->setParameter('role', self::ROLE_ADMIN)
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map(
            static fn (string $userId): UserId => UserId::fromString($userId),
            $userIds,
        );
    }
}
