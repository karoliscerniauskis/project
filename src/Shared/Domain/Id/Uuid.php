<?php

declare(strict_types=1);

namespace App\Shared\Domain\Id;

abstract readonly class Uuid
{
    final public function __construct(
        private string $value,
    ) {
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    final public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
