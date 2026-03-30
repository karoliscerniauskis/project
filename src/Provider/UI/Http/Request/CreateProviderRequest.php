<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateProviderRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required.')]
        public string $name,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['name']) && is_string($payload['name']) ? $payload['name'] : '',
        );
    }
}
