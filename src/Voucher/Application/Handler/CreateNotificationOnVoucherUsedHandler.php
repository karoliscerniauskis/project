<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherUsed;

final readonly class CreateNotificationOnVoucherUsedHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(VoucherUsed $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getIssuedToEmail());

        if ($userId === null) {
            return;
        }

        $this->notificationSender->send(
            $userId,
            'voucher_used',
            'Voucher used',
            sprintf(
                'Your voucher "%s" has been used.',
                $event->getVoucherCode(),
            ),
            [
                'voucherCode' => $event->getVoucherCode(),
                'url' => $this->frontendUrlCreator->myVouchers(),
            ],
        );
    }
}
