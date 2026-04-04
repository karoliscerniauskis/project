<?php

declare(strict_types=1);

namespace App\Shared\Application\Exception;

use RuntimeException;

abstract class AbstractApiException extends RuntimeException
{
    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    protected function __construct(
        string $message,
        private readonly array $errors,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<int, array{field: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array{field: string, message: string}
     */
    protected static function getError(string $field, string $message): array
    {
        return [
            'field' => $field,
            'message' => $message,
        ];
    }
}
