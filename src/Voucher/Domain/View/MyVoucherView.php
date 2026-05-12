<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use App\Shared\Domain\View\ArrayableView;

/**
 * @implements ArrayableView<array{id: string, code: string|null, providerId: string, providerName: string, status: string, canBeClaimed: bool, canBeTransferred: bool, canProviderBeChanged: bool, isCodeVisible: bool, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null, expiresAt: string|null}>
 */
final readonly class MyVoucherView implements ArrayableView
{
    public function __construct(
        private string $id,
        private string $code,
        private string $providerId,
        private string $providerName,
        private string $status,
        private bool $canBeClaimed,
        private bool $canBeTransferred,
        private bool $canProviderBeChanged,
        private bool $isCodeVisible,
        private string $type,
        private ?int $initialAmount,
        private ?int $remainingAmount,
        private ?int $initialUsages,
        private ?int $remainingUsages,
        private ?string $expiresAt,
    ) {
    }

    /**
     * @return array{id: string, code: string|null, providerId: string, providerName: string, status: string, canBeClaimed: bool, canBeTransferred: bool, canProviderBeChanged: bool, isCodeVisible: bool, type: string, initialAmount: int|null, remainingAmount: int|null, initialUsages: int|null, remainingUsages: int|null, expiresAt: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->isCodeVisible ? $this->code : null,
            'providerId' => $this->providerId,
            'providerName' => $this->providerName,
            'status' => $this->status,
            'canBeClaimed' => $this->canBeClaimed,
            'canBeTransferred' => $this->canBeTransferred,
            'canProviderBeChanged' => $this->canProviderBeChanged,
            'isCodeVisible' => $this->isCodeVisible,
            'type' => $this->type,
            'initialAmount' => $this->initialAmount,
            'remainingAmount' => $this->remainingAmount,
            'initialUsages' => $this->initialUsages,
            'remainingUsages' => $this->remainingUsages,
            'expiresAt' => $this->expiresAt,
        ];
    }
}
