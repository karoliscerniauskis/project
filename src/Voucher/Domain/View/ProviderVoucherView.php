<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string}>
 */
final readonly class ProviderVoucherView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $code,
        private string $issuedToEmail,
        private ?string $claimedByUser,
        private string $createdByUser,
        private string $status,
    ) {
    }

    /**
     * @return array{id: string, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'issuedToEmail' => $this->issuedToEmail,
            'claimedByUser' => $this->claimedByUser,
            'createdByUser' => $this->createdByUser,
            'status' => $this->status,
        ];
    }
}
