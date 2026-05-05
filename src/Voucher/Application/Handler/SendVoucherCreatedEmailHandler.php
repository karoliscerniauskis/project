<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Email\EmailSender;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherCreated;

final readonly class SendVoucherCreatedEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(VoucherCreated $event): void
    {
        $myVouchersUrl = $this->frontendUrlCreator->myVouchers();
        $this->emailSender->send(
            $this->emailFrom,
            $event->getIssuedToEmail(),
            'You have received a voucher',
            'You have received a voucher. View it here: '.$myVouchersUrl,
        );
    }
}
