<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Id;

use App\Shared\Domain\Id\UuidValidator;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final readonly class SymfonyUuidValidator implements UuidValidator
{
    public function isValid(string $value): bool
    {
        return SymfonyUuid::isValid($value);
    }
}
