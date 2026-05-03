<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RefreshTokenResponse',
    required: ['token'],
    properties: [
        new OA\Property(
            property: 'token',
            type: 'string',
            example: 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...',
        ),
    ],
    type: 'object',
)]
final readonly class RefreshTokenResponse
{
}
