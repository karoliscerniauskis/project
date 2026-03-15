<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Repository;

use App\Auth\Domain\Entity\User;
use App\Auth\Domain\Repository\UserRepository;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $existing = $this->entityManager->getRepository(UserRecord::class)->find($user->getId()->toString());

        if ($existing instanceof UserRecord) {
            $existing->setEmail($user->getEmail());
            $existing->setPendingEmail($user->getPendingEmail());
            $existing->setEmailVerificationSlug($user->getEmailVerificationSlug());
            $existing->setEmailVerifiedAt($user->getEmailVerifiedAt());

            $this->entityManager->flush();

            return;
        }

        $userRecord = new UserRecord(
            $user->getId()->toString(),
            $user->getEmail(),
            $user->getPendingEmail(),
            $user->getHashedPassword(),
            $user->getRoles(),
            $user->getEmailVerificationSlug(),
            $user->getEmailVerifiedAt(),
        );
        $this->entityManager->persist($userRecord);
        $this->entityManager->flush();
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

    private function toDomainUser(UserRecord $userRecord): User
    {
        return User::reconstitute(
            UserId::fromString($userRecord->getId()),
            $userRecord->getEmail(),
            $userRecord->getPendingEmail(),
            $userRecord->getHashedPassword(),
            $userRecord->getRoles(),
            $userRecord->getEmailVerificationSlug(),
            $userRecord->getEmailVerifiedAt(),
        );
    }

    private function toDomainUserOrNull(?UserRecord $userRecord): ?User
    {
        if (!$userRecord instanceof UserRecord) {
            return null;
        }

        return $this->toDomainUser($userRecord);
    }
}
