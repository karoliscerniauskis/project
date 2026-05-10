<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'LinkProviderRequest',
    required: ['linkedProviderId'],
    properties: [
        new OA\Property(
            property: 'linkedProviderId',
            description: 'Provider ID to link with',
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369'
        ),
    ],
    type: 'object',
)]
final readonly class LinkProviderRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Linked provider ID is required.')]
        #[Assert\Uuid(message: 'Invalid UUID format.')]
        public string $linkedProviderId,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['linkedProviderId']) && is_string($payload['linkedProviderId']) ? $payload['linkedProviderId'] : '',
        );
    }
}
