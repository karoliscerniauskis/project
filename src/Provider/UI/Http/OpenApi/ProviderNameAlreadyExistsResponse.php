<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderNameAlreadyExistsResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Provider name is already taken.',
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
                        example: 'name',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Provider "Coffee House" already exists.',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class ProviderNameAlreadyExistsResponse
{
}
