<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'InviteProviderUserRequest',
    required: ['email'],
    type: 'object',
)]
final readonly class InviteProviderUserRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'Email must be valid.')]
        #[OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'member@example.com',
        )]
        public string $email,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['email']) && is_string($payload['email']) ? $payload['email'] : '',
        );
    }
}
