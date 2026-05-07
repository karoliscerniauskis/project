<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UnreadNotificationsCountResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            required: ['count'],
            properties: [
                new OA\Property(
                    property: 'count',
                    type: 'integer',
                    example: 3,
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final readonly class UnreadNotificationsCountResponse
{
}
