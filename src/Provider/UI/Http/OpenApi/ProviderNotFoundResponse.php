<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderNotFoundResponse',
    required: ['message'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Provider not found.',
        ),
    ],
    type: 'object',
)]
final readonly class ProviderNotFoundResponse
{
}
