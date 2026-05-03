<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderUsersResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'email', 'role', 'status'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'string',
                        example: 'provider-user-id',
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'member@example.com',
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        example: 'member',
                    ),
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        example: 'active',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class ProviderUsersResponse
{
}
