<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProvidersResponse',
    required: ['data', 'pagination'],
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
        new OA\Property(
            property: 'pagination',
            required: ['page', 'limit', 'total', 'totalPages'],
            properties: [
                new OA\Property(
                    property: 'page',
                    type: 'integer',
                    example: 1,
                ),
                new OA\Property(
                    property: 'limit',
                    type: 'integer',
                    example: 10,
                ),
                new OA\Property(
                    property: 'total',
                    type: 'integer',
                    example: 42,
                ),
                new OA\Property(
                    property: 'totalPages',
                    type: 'integer',
                    example: 5,
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final readonly class ProvidersResponse
{
}
