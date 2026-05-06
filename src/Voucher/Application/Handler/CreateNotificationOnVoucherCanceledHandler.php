<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherCanceled;

final readonly class CreateNotificationOnVoucherCanceledHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(VoucherCanceled $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getIssuedToEmail());

        if ($userId === null) {
            return;
        }

        $this->notificationSender->send(
            $userId,
            'voucher_canceled',
            'Your voucher has been canceled',
            sprintf('Your voucher "%s" has been canceled.', $event->getVoucherCode()),
            [
                'voucherCode' => $event->getVoucherCode(),
                'url' => $this->frontendUrlCreator->myVouchers(),
            ],
        );
    }
}
