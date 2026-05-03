<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderVouchersResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['code', 'issuedToEmail', 'claimedByUser', 'createdByUser', 'status'],
                properties: [
                    new OA\Property(
                        property: 'code',
                        type: 'string',
                        example: 'ABC123XYZ',
                    ),
                    new OA\Property(
                        property: 'issuedToEmail',
                        type: 'string',
                        example: 'user@example.com',
                    ),
                    new OA\Property(
                        property: 'claimedByUser',
                        type: 'string',
                        example: 'john.doe@example.com',
                        nullable: true,
                    ),
                    new OA\Property(
                        property: 'createdByUser',
                        type: 'string',
                        example: 'admin@example.com',
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
final readonly class ProviderVouchersResponse
{
}
