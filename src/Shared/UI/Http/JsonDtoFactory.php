<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class JsonDtoFactory
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param class-string<JsonMappableRequest> $dtoClass
     */
    public function create(Request $request, string $dtoClass): JsonMappableRequest
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidJsonPayloadException('Invalid JSON payload.', previous: $exception);
        }

        if (!is_array($payload)) {
            throw new InvalidJsonPayloadException('Invalid request payload.');
        }

        /** @var array<string, mixed> $payload */
        $dto = $dtoClass::fromArray($payload);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw ValidationException::fromViolations($violations);
        }

        return $dto;
    }
}
