<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InvalidCredentialsResponse',
    required: ['message'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Invalid credentials.',
        ),
    ],
    type: 'object',
)]
final readonly class InvalidCredentialsResponse
{
}
