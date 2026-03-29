<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeUserPasswordRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Current password is required.')]
        public string $currentPassword,

        #[Assert\NotBlank(message: 'New password is required.')]
        #[Assert\Length(
            min: 8,
            minMessage: 'New password must be at least {{ limit }} characters long.'
        )]
        public string $newPassword,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            isset($payload['currentPassword']) && is_string($payload['currentPassword']) ? $payload['currentPassword'] : '',
            isset($payload['newPassword']) && is_string($payload['newPassword']) ? $payload['newPassword'] : '',
        );
    }
}
