<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Repository;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Auth\Infrastructure\Doctrine\Mapper\UserRecordMapper;
use App\Shared\Application\User\UserIdFinder;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository, UserIdFinder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRecordMapper $userRecordMapper,
    ) {
    }

    public function save(User $user): void
    {
        $existing = $this->entityManager->getRepository(UserRecord::class)->find($user->getId()->toString());

        if ($existing instanceof UserRecord) {
            $this->userRecordMapper->syncRecord($user, $existing);

            return;
        }

        $this->entityManager->persist($this->userRecordMapper->toRecord($user));
    }

    public function findByEmail(string $email): ?User
    {
        $userRecord = $this->entityManager->getRepository(UserRecord::class)->findOneBy(['email' => $email]);

        return $this->toDomainUserOrNull($userRecord);
    }

    public function findByPendingEmail(string $pendingEmail): ?User
    {
        $userRecord = $this->entityManager->getRepository(UserRecord::class)->findOneBy([
            'pendingEmail' => $pendingEmail,
        ]);

        return $this->toDomainUserOrNull($userRecord);
    }

    public function findByEmailVerificationSlug(string $emailVerificationSlug): ?User
    {
        $userRecord = $this->entityManager->getRepository(UserRecord::class)->findOneBy([
            'emailVerificationSlug' => $emailVerificationSlug,
        ]);

        return $this->toDomainUserOrNull($userRecord);
    }

    public function findById(UserId $id): ?User
    {
        $userRecord = $this->entityManager->getRepository(UserRecord::class)->find($id->toString());

        return $this->toDomainUserOrNull($userRecord);
    }

    private function toDomainUserOrNull(?UserRecord $userRecord): ?User
    {
        if (!$userRecord instanceof UserRecord) {
            return null;
        }

        return $this->userRecordMapper->toDomain($userRecord);
    }

    public function findIdByEmail(string $email): ?UserId
    {
        $user = $this->findByEmail($email);

        if (!$user instanceof User) {
            return null;
        }

        return $user->getId();
    }

    /**
     * @return User[]
     */
    public function findEmailBreachCheckCandidates(DateTimeImmutable $recheckThreshold, int $limit): array
    {
        /** @var UserRecord[] $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('user')
            ->from(UserRecord::class, 'user')
            ->andWhere('user.emailBreachCheckEnabled = true')
            ->andWhere('user.emailBreachCheckedAt IS NULL OR user.emailBreachCheckedAt <= :recheckThreshold')
            ->setParameter('recheckThreshold', $recheckThreshold)
            ->orderBy('user.emailBreachCheckedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (UserRecord $record): User => $this->userRecordMapper->toDomain($record),
            $records,
        );
    }
}
