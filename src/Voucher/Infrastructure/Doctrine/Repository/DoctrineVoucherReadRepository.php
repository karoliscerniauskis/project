<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Repository;

use App\Shared\Domain\Id\ProviderId;
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
        /** @var array<int, array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'v.code AS code',
                'v.issuedToEmail AS issuedToEmail',
                'claimedUser.email AS claimedByUser',
                'createdUser.email AS createdByUser',
                'v.status AS status',
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
                $row['status'],
            );
        }

        return new ProviderVouchersView($vouchers);
    }

    public function findByUserEmailAndUserId(string $email, string $userId): MyVouchersView
    {
        /** @var array<int, array{id: UuidV7, code: string, claimedByUserId: UuidV7|null, providerName: string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'v.id AS id',
                'v.code AS code',
                'v.claimedByUserId AS claimedByUserId',
                'p.name AS providerName',
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
            $vouchers[] = new MyVoucherView(
                $row['id']->toRfc4122(),
                $row['claimedByUserId']?->toRfc4122() === $userId ? $row['code'] : null,
                $row['providerName'],
            );
        }

        return new MyVouchersView($vouchers);
    }
}
