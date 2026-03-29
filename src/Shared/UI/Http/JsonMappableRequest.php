<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

interface JsonMappableRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self;
}
