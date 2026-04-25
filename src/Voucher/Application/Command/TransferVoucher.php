<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class TransferVoucher
{
    public function __construct(
        private string $voucherId,
        private string $currentUserEmail,
        private string $recipientEmail,
    ) {
    }

    public function getVoucherId(): string
    {
        return $this->voucherId;
    }

    public function getCurrentUserEmail(): string
    {
        return $this->currentUserEmail;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }
}
