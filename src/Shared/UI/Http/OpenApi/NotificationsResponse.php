<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationsResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'type', 'title', 'message', 'payload', 'readAt', 'createdAt'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'string',
                        example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        example: 'voucher_created',
                    ),
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'New Voucher Created',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'A new voucher has been created for you.',
                    ),
                    new OA\Property(
                        property: 'payload',
                        type: 'object',
                        example: ['voucherId' => '019d882d-1d68-7e2f-94ce-0cd2f4d0c369'],
                    ),
                    new OA\Property(
                        property: 'readAt',
                        type: 'string',
                        example: '2026-05-03T15:30:00+00:00',
                        nullable: true,
                    ),
                    new OA\Property(
                        property: 'createdAt',
                        type: 'string',
                        example: '2026-05-03T14:00:00+00:00',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class NotificationsResponse
{
}
