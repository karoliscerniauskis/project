<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Id;

use App\Shared\Domain\Id\UuidCreator;
use Symfony\Component\Uid\Uuid;

final readonly class SymfonyUuidCreator implements UuidCreator
{
    public function create(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
