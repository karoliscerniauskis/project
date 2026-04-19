<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string}>
 */
final readonly class ProviderVoucherView implements ArrayableView
{
    public function __construct(
        private string $code,
        private string $issuedToEmail,
        private ?string $claimedByUser,
        private string $createdByUser,
    ) {
    }

    /**
     * @return array{code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'issuedToEmail' => $this->issuedToEmail,
            'claimedByUser' => $this->claimedByUser,
            'createdByUser' => $this->createdByUser,
        ];
    }
}
