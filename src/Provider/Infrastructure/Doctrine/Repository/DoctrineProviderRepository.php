<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Provider\Infrastructure\Doctrine\Mapper\ProviderRecordMapper;
use App\Shared\Domain\Id\ProviderId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderRepository implements ProviderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProviderRecordMapper $providerRecordMapper,
    ) {
    }

    public function save(Provider $provider): void
    {
        $existing = $this->entityManager->getRepository(ProviderRecord::class)->find($provider->getId()->toString());

        if ($existing instanceof ProviderRecord) {
            $this->providerRecordMapper->syncRecord($provider, $existing);

            return;
        }

        $this->entityManager->persist($this->providerRecordMapper->toRecord($provider));
    }

    public function findById(ProviderId $id): ?Provider
    {
        $record = $this->entityManager->getRepository(ProviderRecord::class)->find($id->toString());

        if (!$record instanceof ProviderRecord) {
            return null;
        }

        return $this->providerRecordMapper->toDomain($record);
    }
}
