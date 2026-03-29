<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends RuntimeException
{
    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    private function __construct(
        private readonly array $errors,
    ) {
        parent::__construct('Validation failed.');
    }

    /**
     * @return array<int, array{field: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fromViolations(ConstraintViolationListInterface $violations): self
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        return new self($errors);
    }
}
