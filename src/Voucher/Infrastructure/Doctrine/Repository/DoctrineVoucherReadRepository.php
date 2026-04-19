<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\ProviderId;
use App\Voucher\Domain\Repository\VoucherReadRepository;
use App\Voucher\Domain\View\ProviderVouchersView;
use App\Voucher\Domain\View\ProviderVoucherView;
use App\Voucher\Infrastructure\Doctrine\Entity\VoucherRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVoucherReadRepository implements VoucherReadRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByProviderId(ProviderId $providerId): ProviderVouchersView
    {
        /** @var array<int, array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'v.code AS code',
                'v.issuedToEmail AS issuedToEmail',
                'claimedUser.email AS claimedByUser',
                'createdUser.email AS createdByUser',
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
                $row['code'],
                $row['issuedToEmail'],
                $row['claimedByUser'],
                $row['createdByUser'],
            );
        }

        return new ProviderVouchersView($vouchers);
    }
}
