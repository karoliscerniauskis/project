<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Slug;

abstract readonly class AbstractRandomSlugGenerator
{
    final public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }
}
