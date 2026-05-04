<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use RuntimeException;

final class InvalidRequestParameterException extends RuntimeException
{
    public function __construct(
        private readonly string $field,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
