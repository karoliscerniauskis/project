<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoucherAccessDeniedResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Forbidden.',
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
                        example: 'voucher',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'You are not allowed to perform this action.',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class VoucherAccessDeniedResponse
{
}
