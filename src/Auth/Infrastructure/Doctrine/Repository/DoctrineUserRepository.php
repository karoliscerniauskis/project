<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Repository;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $userRecord = new UserRecord($user->getId(), $user->getEmail(), $user->getHashedPassword(), $user->getRoles());
        $this->entityManager->persist($userRecord);
        $this->entityManager->flush();
    }

    public function findByEmail(string $email): ?User
    {
        $userRecord = $this->entityManager->getRepository(UserRecord::class)->findOneBy(['email' => $email]);

        if (!$userRecord instanceof UserRecord) {
            return null;
        }

        return User::reconstitute(
            $userRecord->getId(),
            $userRecord->getEmail(),
            $userRecord->getHashedPassword(),
            $userRecord->getRoles(),
        );
    }
}
