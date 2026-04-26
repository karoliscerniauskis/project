<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\User\UserIdFinder;
use App\Voucher\Domain\Event\VoucherCreated;

final readonly class CreateNotificationOnVoucherCreatedHandler
{
    public function __construct(
        private UserIdFinder $userIdFinder,
        private NotificationSender $notificationSender,
    ) {
    }

    public function __invoke(VoucherCreated $event): void
    {
        $userId = $this->userIdFinder->findIdByEmail($event->getIssuedToEmail());

        if (null === $userId) {
            return;
        }

        $this->notificationSender->send(
            $userId,
            'voucher_created',
            'You have received a voucher',
            'A new voucher has been created for you.',
            [
                'providerId' => $event->getProviderId(),
            ],
        );
    }
}
