<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null}>
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
        private string $type,
        private ?int $initialAmount,
        private ?int $remainingAmount,
        private ?int $initialUsages,
        private ?int $remainingUsages,
    ) {
    }

    /**
     * @return array{id: string, code: string, issuedToEmail: string, claimedByUser: string|null, createdByUser: string, status: string, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null}
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
            'type' => $this->type,
            'initialAmount' => $this->initialAmount,
            'remainingAmount' => $this->remainingAmount,
            'initialUsages' => $this->initialUsages,
            'remainingUsages' => $this->remainingUsages,
        ];
    }
}
