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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\UuidV7;

final readonly class DoctrineVoucherReadRepository implements VoucherReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByProviderId(ProviderId $providerId): ProviderVouchersView
    {
        /** @var array<int, array{id: UuidV7, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
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
            ->setParameter('providerId', $providerId->toString())
            ->getQuery()
            ->getArrayResult();

        $vouchers = [];

        foreach ($rows as $row) {
            $vouchers[] = new ProviderVoucherView(
                $row['id']->toRfc4122(),
                $row['status'] !== VoucherStatus::Active->value ? $row['code'] : sprintf('***%s', substr($row['code'], -3)),
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

    public function findByUserEmailAndUserId(string $email, string $userId): MyVouchersView
    {
        /** @var array<int, array{id: UuidV7, code: string, claimedByUserId: UuidV7|null, providerName: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'v.id AS id',
                'v.code AS code',
                'v.claimedByUserId AS claimedByUserId',
                'p.name AS providerName',
                'v.status as status',
                'v.type AS type',
                'v.initialAmount AS initialAmount',
                'v.remainingAmount AS remainingAmount',
                'v.initialUsages AS initialUsages',
                'v.remainingUsages AS remainingUsages',
            )
            ->from(VoucherRecord::class, 'v')
            ->innerJoin(
                'App\\Provider\\Infrastructure\\Doctrine\\Entity\\ProviderRecord',
                'p',
                'WITH',
                'p.id = v.providerId',
            )
            ->andWhere('v.issuedToEmail = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getArrayResult();

        $vouchers = [];

        foreach ($rows as $row) {
            $claimedByCurrentUser = $row['claimedByUserId']?->toRfc4122() === $userId;
            $canBeClaimedOrTransferred = $row['status'] === VoucherStatus::Active->value && !$claimedByCurrentUser;
            $vouchers[] = new MyVoucherView(
                $row['id']->toRfc4122(),
                $canBeClaimedOrTransferred ? sprintf('***%s', substr($row['code'], -3)) : $row['code'],
                $row['providerName'],
                $row['status'],
                $canBeClaimedOrTransferred,
                $row['type'],
                $row['initialAmount'],
                $row['remainingAmount'],
                $row['initialUsages'],
                $row['remainingUsages'],
            );
        }

        return new MyVouchersView($vouchers);
    }
}
