<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, code: string, providerName: string, status: string, canBeClaimedOrTransferred: bool}>
 */
final readonly class MyVoucherView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $code,
        private string $providerName,
        private string $status,
        private bool $canBeClaimedOrTransferred,
    ) {
    }

    /**
     * @return array{id: string, code: string, providerName: string, status: string, canBeClaimedOrTransferred: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'providerName' => $this->providerName,
            'status' => $this->status,
            'canBeClaimedOrTransferred' => $this->canBeClaimedOrTransferred,
        ];
    }
}
