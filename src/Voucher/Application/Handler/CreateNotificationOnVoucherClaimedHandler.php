<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\ProviderUser\ProviderAdminFinder;
use App\Shared\Domain\Id\ProviderId;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherClaimed;

final readonly class CreateNotificationOnVoucherClaimedHandler
{
    public function __construct(
        private ProviderAdminFinder $providerAdminFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(VoucherClaimed $event): void
    {
        $adminUserIds = $this->providerAdminFinder->findAdminUserIdsByProviderId(
            ProviderId::fromString($event->getProviderId()),
        );
        $voucherCode = sprintf('***%s', substr($event->getVoucherCode(), -3));

        foreach ($adminUserIds as $adminUserId) {
            $this->notificationSender->send(
                $adminUserId,
                'voucher_claimed',
                'Voucher claimed',
                sprintf(
                    'Voucher "%s" issued to %s has been claimed.',
                    $voucherCode,
                    $event->getIssuedToEmail(),
                ),
                [
                    'providerId' => $event->getProviderId(),
                    'voucherCode' => $voucherCode,
                    'issuedToEmail' => $event->getIssuedToEmail(),
                    'url' => $this->frontendUrlCreator->providerVouchers($event->getProviderId()),
                ],
            );
        }
    }
}
