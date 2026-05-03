<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoucherValidationResponse',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            required: ['valid', 'status', 'reason'],
            properties: [
                new OA\Property(
                    property: 'valid',
                    type: 'boolean',
                    example: true,
                ),
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    example: 'valid',
                ),
                new OA\Property(
                    property: 'reason',
                    type: 'string',
                    example: null,
                    nullable: true,
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
final readonly class VoucherValidationResponse
{
}
