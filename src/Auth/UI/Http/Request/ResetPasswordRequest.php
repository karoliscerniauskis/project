<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Reset token is required.')]
        public string $resetToken,

        #[Assert\NotBlank(message: 'New password is required.')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long.'
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
            isset($payload['resetToken']) && is_string($payload['resetToken']) ? $payload['resetToken'] : '',
            isset($payload['newPassword']) && is_string($payload['newPassword']) ? $payload['newPassword'] : '',
        );
    }
}
