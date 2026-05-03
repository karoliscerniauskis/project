<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'CreateProviderRequest',
    required: ['name'],
    type: 'object',
)]
final readonly class CreateProviderRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required.')]
        #[OA\Property(
            property: 'name',
            description: 'Provider name.',
            type: 'string',
            example: 'Coffee House',
        )]
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
