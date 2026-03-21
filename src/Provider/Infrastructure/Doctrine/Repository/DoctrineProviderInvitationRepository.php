<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderInvitationRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderInvitationRepository implements ProviderInvitationRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ProviderInvitation $invitation): void
    {
        $existing = $this
            ->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->find($invitation->getId()->toString());

        if ($existing instanceof ProviderInvitationRecord) {
            $existing->setStatus($invitation->getStatus()->value);
            $existing->setAcceptedUserId($invitation->getAcceptedUserId()?->toString());
            $existing->setAcceptedAt($invitation->getAcceptedAt());

            $this->entityManager->flush();

            return;
        }

        $record = new ProviderInvitationRecord(
            $invitation->getId()->toString(),
            $invitation->getProviderId()->toString(),
            $invitation->getEmail(),
            $invitation->getRole()->value,
            $invitation->getSlug(),
            $invitation->getStatus()->value,
            $invitation->getInvitedByUserId()->toString(),
            $invitation->getAcceptedUserId()?->toString(),
            $invitation->getCreatedAt(),
            $invitation->getAcceptedAt(),
            $invitation->getExpiresAt(),
        );

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findBySlug(string $slug): ?ProviderInvitation
    {
        $record = $this->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->findOneBy(['slug' => $slug]);

        if (!$record instanceof ProviderInvitationRecord) {
            return null;
        }

        return $this->toDomain($record);
    }

    public function findPendingByProviderIdAndEmail(ProviderId $providerId, string $email): ?ProviderInvitation
    {
        $record = $this->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'email' => $email,
                'status' => ProviderInvitationStatus::Pending->value,
            ]);

        if (!$record instanceof ProviderInvitationRecord) {
            return null;
        }

        return $this->toDomain($record);
    }

    public function existsAcceptedByProviderIdAndEmail(ProviderId $providerId, string $email): bool
    {
        $record = $this->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'email' => $email,
                'status' => ProviderInvitationStatus::Accepted->value,
            ]);

        return $record instanceof ProviderInvitationRecord;
    }

    private function toDomain(ProviderInvitationRecord $record): ProviderInvitation
    {
        return ProviderInvitation::reconstitute(
            ProviderInvitationId::fromString($record->getId()),
            ProviderId::fromString($record->getProviderId()),
            $record->getEmail(),
            ProviderUserRole::from($record->getRole()),
            $record->getSlug(),
            ProviderInvitationStatus::from($record->getStatus()),
            UserId::fromString($record->getInvitedByUserId()),
            $record->getAcceptedUserId() !== null ? UserId::fromString($record->getAcceptedUserId()) : null,
            $record->getCreatedAt(),
            $record->getAcceptedAt(),
            $record->getExpiresAt(),
        );
    }
}
