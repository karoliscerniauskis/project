<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Voucher\Domain\Enum\VoucherStatus;
use App\Voucher\Domain\Repository\VoucherReadRepository;
use App\Voucher\Domain\View\MyVouchersView;
use App\Voucher\Domain\View\MyVoucherView;
use App\Voucher\Domain\View\ProviderVouchersView;
use App\Voucher\Domain\View\ProviderVoucherView;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\UuidV7;

final readonly class DoctrineVoucherReadRepository implements VoucherReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByProviderId(
        ProviderId $providerId,
        ?string $codeFilter = null,
        int $limit = 20,
        int $offset = 0,
    ): ProviderVouchersView {
        $qb = $this->entityManager->createQueryBuilder()
            ->select(
                'v.id AS id',
                'v.code AS code',
                'v.issuedToEmail AS issuedToEmail',
                'claimedUser.email AS claimedByUser',
                'createdUser.email AS createdByUser',
                'v.status AS status',
                'v.type AS type',
                'v.initialAmount AS initialAmount',
                'v.remainingAmount AS remainingAmount',
                'v.initialUsages AS initialUsages',
                'v.remainingUsages AS remainingUsages',
            )
            ->from(VoucherRecord::class, 'v')
            ->leftJoin(
                'App\\Auth\\Infrastructure\\Doctrine\\Entity\\UserRecord',
                'claimedUser',
                'WITH',
                'claimedUser.id = v.claimedByUserId',
            )
            ->innerJoin(
                'App\\Provider\\Infrastructure\\Doctrine\\Entity\\ProviderUserRecord',
                'createdProviderUser',
                'WITH',
                'createdProviderUser.id = v.createdByProviderUserId',
            )
            ->innerJoin(
                'App\\Auth\\Infrastructure\\Doctrine\\Entity\\UserRecord',
                'createdUser',
                'WITH',
                'createdUser.id = createdProviderUser.userId',
            )
            ->andWhere('v.providerId = :providerId')
            ->setParameter('providerId', $providerId->toString());

        if ($codeFilter !== null) {
            $qb->andWhere('v.code LIKE :codeFilter')
                ->setParameter('codeFilter', '%'.$codeFilter.'%');
        }

        /** @var array<int, array{id: UuidV7, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null}> $rows */
        $rows = $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();

        $vouchers = [];

        foreach ($rows as $row) {
            $vouchers[] = new ProviderVoucherView(
                $row['id']->toRfc4122(),
                $row['code'],
                $row['issuedToEmail'],
                $row['claimedByUser'],
                $row['createdByUser'],
                $row['status'],
                $row['type'],
                $row['initialAmount'],
                $row['remainingAmount'],
                $row['initialUsages'],
                $row['remainingUsages'],
            );
        }

        return new ProviderVouchersView($vouchers);
    }

    public function findByUserEmailAndUserId(
        string $email,
        string $userId,
        ?string $codeFilter = null,
        int $limit = 20,
        int $offset = 0,
    ): MyVouchersView {
        $qb = $this->entityManager->createQueryBuilder()
            ->select(
                'v.id AS id',
                'v.code AS code',
                'v.providerId AS providerId',
                'v.claimedByUserId AS claimedByUserId',
                'p.name AS providerName',
                'v.status as status',
                'v.type AS type',
                'v.initialAmount AS initialAmount',
                'v.remainingAmount AS remainingAmount',
                'v.initialUsages AS initialUsages',
                'v.remainingUsages AS remainingUsages',
                'v.expiresAt AS expiresAt',
            )
            ->from(VoucherRecord::class, 'v')
            ->innerJoin(
                'App\\Provider\\Infrastructure\\Doctrine\\Entity\\ProviderRecord',
                'p',
                'WITH',
                'p.id = v.providerId',
            )
            ->andWhere('v.issuedToEmail = :email')
            ->andWhere('v.sentAt IS NOT NULL')
            ->setParameter('email', $email);

        if ($codeFilter !== null) {
            $qb->andWhere('v.code LIKE :codeFilter')
                ->setParameter('codeFilter', '%'.$codeFilter.'%');
        }

        /** @var array<int, array{id: UuidV7, code: string, providerId: UuidV7, claimedByUserId: UuidV7|null, providerName: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null, expiresAt: DateTimeImmutable|null}> $rows */
        $rows = $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();

        $vouchers = [];

        foreach ($rows as $row) {
            $claimedByCurrentUser = $row['claimedByUserId']?->toRfc4122() === $userId;
            $canBeClaimed = $row['status'] === VoucherStatus::Active->value && !$claimedByCurrentUser;
            $canBeTransferred = $row['status'] === VoucherStatus::Active->value && $claimedByCurrentUser;
            $canProviderBeChanged = $row['status'] === VoucherStatus::Active->value && $claimedByCurrentUser;
            $isCodeVisible = $claimedByCurrentUser;
            $vouchers[] = new MyVoucherView(
                $row['id']->toRfc4122(),
                $row['code'],
                $row['providerId']->toRfc4122(),
                $row['providerName'],
                $row['status'],
                $canBeClaimed,
                $canBeTransferred,
                $canProviderBeChanged,
                $isCodeVisible,
                $row['type'],
                $row['initialAmount'],
                $row['remainingAmount'],
                $row['initialUsages'],
                $row['remainingUsages'],
                $row['expiresAt']?->format('Y-m-d\TH:i:s\Z'),
            );
        }

        return new MyVouchersView($vouchers);
    }

    public function countByProviderId(ProviderId $providerId, ?string $codeFilter = null): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(v.id)')
            ->from(VoucherRecord::class, 'v')
            ->andWhere('v.providerId = :providerId')
            ->setParameter('providerId', $providerId->toString());

        if ($codeFilter !== null) {
            $qb->andWhere('v.code LIKE :codeFilter')
                ->setParameter('codeFilter', '%'.$codeFilter.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countByUserEmailAndUserId(string $email, string $userId, ?string $codeFilter = null): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(v.id)')
            ->from(VoucherRecord::class, 'v')
            ->andWhere('v.issuedToEmail = :email')
            ->andWhere('v.sentAt IS NOT NULL')
            ->setParameter('email', $email);

        if ($codeFilter !== null) {
            $qb->andWhere('v.code LIKE :codeFilter')
                ->setParameter('codeFilter', '%'.$codeFilter.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
