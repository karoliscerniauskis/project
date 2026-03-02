<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Doctrine\Repository;

use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProviderRepository implements ProviderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Provider $provider): void
    {
        $record = new ProviderRecord(
            $provider->getId(),
            $provider->getName(),
            $provider->getStatus(),
        );
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Provider
    {
        $record = $this->entityManager->getRepository(ProviderRecord::class)->find($id);

        if (!$record instanceof ProviderRecord) {
            return null;
        }

        return Provider::reconstitute(
            $record->getId(),
            $record->getName(),
            $record->getStatus(),
        );
    }
}
