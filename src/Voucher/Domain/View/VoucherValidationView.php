<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;
use App\Voucher\Domain\Enum\VoucherValidationReason;
use App\Voucher\Domain\Enum\VoucherValidationStatus;

/**
 * @implements ArrayableView<array{valid: bool, status: string, reason: string|null}>
 */
final readonly class VoucherValidationView implements ArrayableView
{
    public function __construct(
        private bool $valid,
        private VoucherValidationStatus $status,
        private ?VoucherValidationReason $reason = null,
    ) {
    }

    /**
     * @return array{valid: bool, status: string, reason: string|null}
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'status' => $this->status->value,
            'reason' => $this->reason?->value,
        ];
    }
}
