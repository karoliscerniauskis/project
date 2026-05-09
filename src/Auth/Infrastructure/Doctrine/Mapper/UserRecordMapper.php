<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Mapper;

use App\Auth\Domain\Entity\User;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Domain\Id\UserId;

final readonly class UserRecordMapper
{
    public function toDomain(UserRecord $record): User
    {
        return User::reconstitute(
            UserId::fromString($record->getId()),
            $record->getEmail(),
            $record->getPendingEmail(),
            $record->getHashedPassword(),
            $record->getRoles(),
            $record->getEmailVerificationSlug(),
            $record->getEmailVerifiedAt(),
            $record->isEmailBreachCheckEnabled(),
            $record->getEmailBreachCheckedAt(),
            $record->getEmailBreachedAt(),
            $record->getEmailBreachCount(),
        );
    }

    public function toRecord(User $user): UserRecord
    {
        return new UserRecord(
            $user->getId()->toString(),
            $user->getEmail(),
            $user->getPendingEmail(),
            $user->getHashedPassword(),
            $user->getRoles(),
            $user->getEmailVerificationSlug(),
            $user->getEmailVerifiedAt(),
            $user->isEmailBreachCheckEnabled(),
            $user->getEmailBreachCheckedAt(),
            $user->getEmailBreachedAt(),
            $user->getEmailBreachCount(),
        );
    }

    public function syncRecord(User $user, UserRecord $record): void
    {
        $record
            ->setEmail($user->getEmail())
            ->setPendingEmail($user->getPendingEmail())
            ->setHashedPassword($user->getHashedPassword())
            ->setEmailVerificationSlug($user->getEmailVerificationSlug())
            ->setEmailVerifiedAt($user->getEmailVerifiedAt())
            ->setEmailBreachCheckEnabled($user->isEmailBreachCheckEnabled())
            ->setEmailBreachCheckedAt($user->getEmailBreachCheckedAt())
            ->setEmailBreachedAt($user->getEmailBreachedAt())
            ->setEmailBreachCount($user->getEmailBreachCount());
    }
}
