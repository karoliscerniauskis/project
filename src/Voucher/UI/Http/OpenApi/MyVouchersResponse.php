<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MyVouchersResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'code', 'providerName'],
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'string',
                        example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
                    ),
                    new OA\Property(
                        property: 'code',
                        type: 'string',
                        example: 'ABC123XYZ',
                        nullable: true,
                    ),
                    new OA\Property(
                        property: 'providerName',
                        type: 'string',
                        example: 'Coffee House',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class MyVouchersResponse
{
}
