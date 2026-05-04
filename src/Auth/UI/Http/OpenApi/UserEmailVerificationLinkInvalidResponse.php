<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserEmailVerificationLinkInvalidResponse',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Verification link is invalid.',
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
                        example: 'emailVerificationSlug',
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Verification link "email-verification-slug" is invalid or expired.',
                    ),
                ],
                type: 'object',
            ),
        ),
    ],
    type: 'object',
)]
final readonly class UserEmailVerificationLinkInvalidResponse
{
}
