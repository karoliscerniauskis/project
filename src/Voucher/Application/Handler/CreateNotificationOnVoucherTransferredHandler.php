<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherTransferred;

final readonly class CreateNotificationOnVoucherTransferredHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
        private FrontendUrlCreator $frontendUrlCreator,
    ) {
    }

    public function __invoke(VoucherTransferred $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getTransferredToEmail());

        if ($userId === null) {
            return;
        }

        $this->notificationSender->send(
            $userId,
            'voucher_transferred',
            'You have received a voucher',
            sprintf(
                'You have received a voucher from %s.',
                $event->getTransferredFromEmail(),
            ),
            [
                'transferredFromEmail' => $event->getTransferredFromEmail(),
                'url' => $this->frontendUrlCreator->myVouchers(),
            ],
        );
    }
}
