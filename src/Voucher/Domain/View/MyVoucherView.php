<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{code: string, providerName: string}>
 */
final readonly class MyVoucherView implements ArrayableView
{
    public function __construct(
        private string $code,
        private string $providerName,
    ) {
    }

    /**
     * @return array{code: string, providerName: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'providerName' => $this->providerName,
        ];
    }
}
