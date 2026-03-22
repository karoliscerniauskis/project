<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderInvitationRecord;
use App\Provider\Infrastructure\Doctrine\Mapper\ProviderInvitationRecordMapper;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderInvitationRepository implements ProviderInvitationRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProviderInvitationRecordMapper $providerInvitationRecordMapper,
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

            return;
        }

        $this->entityManager->persist($this->providerInvitationRecordMapper->toRecord($invitation));
    }

    public function findBySlug(string $slug): ?ProviderInvitation
    {
        $record = $this->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->findOneBy(['slug' => $slug]);

        if (!$record instanceof ProviderInvitationRecord) {
            return null;
        }

        return $this->providerInvitationRecordMapper->toDomain($record);
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

        return $this->providerInvitationRecordMapper->toDomain($record);
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
}
