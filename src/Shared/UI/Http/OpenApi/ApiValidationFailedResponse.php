<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiValidationFailedResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Validation failed.',
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
                        example: 'Name is required.',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class ApiValidationFailedResponse
{
}
