<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Outbox;

use RuntimeException;

abstract readonly class AbstractOutboxDomainEventFactory
{
    /**
     * @param array<string, mixed> $payload
     */
    protected function stringPayloadValue(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        if (!is_string($value)) {
            throw new RuntimeException(sprintf('Missing or invalid payload key "%s".', $key));
        }

        return $value;
    }
}
