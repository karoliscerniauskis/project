<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\ProviderLink;
use App\Provider\Domain\Repository\ProviderLinkRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderLinkRecord;
use App\Provider\Infrastructure\Doctrine\Mapper\ProviderLinkRecordMapper;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderLinkRepository implements ProviderLinkRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProviderLinkRecordMapper $mapper,
    ) {
    }

    public function save(ProviderLink $providerLink): void
    {
        $this->entityManager->persist($this->mapper->toRecord($providerLink));
    }

    public function delete(ProviderLink $providerLink): void
    {
        $record = $this->entityManager
            ->getRepository(ProviderLinkRecord::class)
            ->find($providerLink->getId()->toString());

        if ($record instanceof ProviderLinkRecord) {
            $this->entityManager->remove($record);
        }
    }

    public function findByProviderAndLinkedProvider(ProviderId $providerId, ProviderId $linkedProviderId): ?ProviderLink
    {
        $record = $this->entityManager
            ->getRepository(ProviderLinkRecord::class)
            ->findOneBy([
                'providerId' => $providerId->toString(),
                'linkedProviderId' => $linkedProviderId->toString(),
            ]);

        return $record instanceof ProviderLinkRecord ? $this->mapper->toDomain($record) : null;
    }

    public function getLinkedProviderIds(ProviderId $providerId): array
    {
        /** @var list<array{linkedProviderId: string}> $results */
        $results = $this->entityManager->createQueryBuilder()
            ->select('pl.linkedProviderId')
            ->from(ProviderLinkRecord::class, 'pl')
            ->where('pl.providerId = :providerId')
            ->setParameter('providerId', $providerId->toString())
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $row): ProviderId => ProviderId::fromString($row['linkedProviderId']),
            $results
        );
    }
}
