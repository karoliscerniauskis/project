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
        private EntityManagerInterface $em,
    ) {
    }

    public function save(User $user): void
    {
        $userRecord = new UserRecord($user->getId(), $user->getEmail());
        $this->em->persist($userRecord);
        $this->em->flush();
    }
}
