<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InvalidPasswordResetTokenResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Invalid or expired password reset token.'),
        new OA\Property(
            property: 'errors',
            type: 'array',
            items: new OA\Items(
                required: ['field', 'message'],
                properties: [
                    new OA\Property(property: 'field', type: 'string', example: 'resetToken'),
                    new OA\Property(property: 'message', type: 'string', example: 'The password reset link is invalid or has expired.'),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
final readonly class InvalidPasswordResetTokenResponse
{
}
