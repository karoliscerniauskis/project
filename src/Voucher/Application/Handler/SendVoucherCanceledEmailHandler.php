<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Email\EmailSender;
use App\Voucher\Application\Url\FrontendUrlCreator;
use App\Voucher\Domain\Event\VoucherCanceled;

final readonly class SendVoucherCanceledEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(VoucherCanceled $event): void
    {
        $this->emailSender->send(
            $this->emailFrom,
            $event->getIssuedToEmail(),
            'Your voucher has been canceled',
            sprintf(
                'Your voucher "%s" has been canceled. View your vouchers here: %s',
                $event->getVoucherCode(),
                $this->frontendUrlCreator->myVouchers(),
            ),
        );
    }
}
