<?php

declare(strict_types=1);

namespace App\Shared\Domain\Id;

interface UuidValidator
{
    public function isValid(string $value): bool;
}
