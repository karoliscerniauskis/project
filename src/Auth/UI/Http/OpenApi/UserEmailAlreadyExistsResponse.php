<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserEmailAlreadyExistsResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Registration failed.',
        ),
        new OA\Property(
            property: 'errors',
            type: 'array',
            items: new OA\Items(
                required: ['field', 'message'],
                properties: [
                    new OA\Property(
                        property: 'field',
                        type: 'string',
                        example: 'email',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Email is already registered.',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class UserEmailAlreadyExistsResponse
{
}
