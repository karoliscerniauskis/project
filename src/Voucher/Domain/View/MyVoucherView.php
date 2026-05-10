<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, code: string, providerId: string, providerName: string, status: string, canBeClaimedOrTransferred: bool, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null, expiresAt: string|null}>
 */
final readonly class MyVoucherView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $code,
        private string $providerId,
        private string $providerName,
        private string $status,
        private bool $canBeClaimedOrTransferred,
        private string $type,
        private ?int $initialAmount,
        private ?int $remainingAmount,
        private ?int $initialUsages,
        private ?int $remainingUsages,
        private ?string $expiresAt,
    ) {
    }

    /**
     * @return array{id: string, code: string, providerId: string, providerName: string, status: string, canBeClaimedOrTransferred: bool, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null, expiresAt: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'providerId' => $this->providerId,
            'providerName' => $this->providerName,
            'status' => $this->status,
            'canBeClaimedOrTransferred' => $this->canBeClaimedOrTransferred,
            'type' => $this->type,
            'initialAmount' => $this->initialAmount,
            'remainingAmount' => $this->remainingAmount,
            'initialUsages' => $this->initialUsages,
            'remainingUsages' => $this->remainingUsages,
            'expiresAt' => $this->expiresAt,
        ];
    }
}
