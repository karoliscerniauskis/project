<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\OpenApi;

use OpenApi\Attributes as OA;

final readonly class UserNotFoundResponse
{
    /**
     * @var array<int, array{field: string, message: string}>
     */
    #[OA\Property(
        example: [
            [
                'field' => 'userId',
                'message' => 'User "019d882d-1d68-7e2f-94ce-0cd2f4d0c369" was not found.',
            ],
        ],
    )]
    public array $errors;

    public function __construct(
        #[OA\Property(example: 'User not found.')]
        public string $message,
    ) {
        $this->errors = [];
    }
}
