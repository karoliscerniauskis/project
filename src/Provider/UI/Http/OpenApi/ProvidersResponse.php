<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProvidersResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'name', 'status', 'isAdmin'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'string',
                        example: 'provider-id',
                    ),
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Coffee House',
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        example: 'active',
                    ),
                    new OA\Property(
                        property: 'isAdmin',
                        type: 'boolean',
                        example: true,
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class ProvidersResponse
{
}
