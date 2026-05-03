<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationNotFoundResponse',
    required: ['message'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Notification not found.',
        ),
    ],
    type: 'object',
)]
final readonly class NotificationNotFoundResponse
{
}
