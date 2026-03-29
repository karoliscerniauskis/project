<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use RuntimeException;
use Throwable;

final class InvalidJsonPayloadException extends RuntimeException
{
    public function __construct(string $message = 'Invalid JSON payload.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
