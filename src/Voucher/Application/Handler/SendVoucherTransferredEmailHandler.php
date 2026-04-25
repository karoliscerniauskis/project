<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Email\EmailSender;
use App\Voucher\Domain\Event\VoucherTransferred;

final readonly class SendVoucherTransferredEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private string $emailFrom,
    ) {
    }

    public function __invoke(VoucherTransferred $event): void
    {
        $this->emailSender->send(
            $this->emailFrom,
            $event->getTransferredToEmail(),
            'You have received a voucher',
            sprintf(
                'You have received a voucher from %s.',
                $event->getTransferredFromEmail(),
            ),
        );
    }
}
