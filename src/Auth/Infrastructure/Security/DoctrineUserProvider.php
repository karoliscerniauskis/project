<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<UserInterface>
 */
final readonly class DoctrineUserProvider implements UserProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === SecurityUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $record = $this->entityManager
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $identifier]);

        if (!$record instanceof UserRecord) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return new SecurityUser(
            $record->getEmail(),
            $record->getHashedPassword(),
            $record->getRoles(),
        );
    }
}
