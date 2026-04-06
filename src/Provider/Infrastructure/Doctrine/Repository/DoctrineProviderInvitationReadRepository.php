<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Repository\ProviderInvitationReadRepository;
use App\Provider\Domain\View\ProviderInvitationsView;
use App\Provider\Domain\View\ProviderInvitationView;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderInvitationRecord;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderInvitationReadRepository implements ProviderInvitationReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findPendingByProviderId(ProviderId $providerId): ProviderInvitationsView
    {
        /** @var ProviderInvitationRecord[] $records */
        $records = $this->entityManager
            ->getRepository(ProviderInvitationRecord::class)
            ->createQueryBuilder('invitation')
            ->where('invitation.providerId = :providerId')
            ->andWhere('invitation.status = :status')
            ->orderBy('invitation.createdAt', 'DESC')
            ->setParameter('providerId', $providerId->toString())
            ->setParameter('status', ProviderInvitationStatus::Pending->value)
            ->getQuery()
            ->getResult();
        $invitations = [];

        foreach ($records as $record) {
            $invitations[] = new ProviderInvitationView(
                $record->getEmail(),
                $record->getCreatedAt(),
                $record->getExpiresAt(),
            );
        }

        return new ProviderInvitationsView($invitations);
    }
}
