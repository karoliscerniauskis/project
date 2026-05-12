<?php

declare(strict_types=1);

namespace App\Voucher\Domain\View;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class VoucherUsageView
{
    public function __construct(
        private string $id,
        private ?int $usedAmount,
        private DateTimeImmutable $usedAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsedAmount(): ?int
    {
        return $this->usedAmount;
    }

    public function getUsedAt(): DateTimeImmutable
    {
        return $this->usedAt;
    }

    /**
     * @return array{
     *     id: string,
     *     usedAmount: int|null,
     *     usedAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usedAmount' => $this->usedAmount,
            'usedAt' => $this->usedAt->format(DateTimeInterface::ATOM),
        ];
    }
}
