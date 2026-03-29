<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'Email must be valid.')]
        public string $email,
        #[Assert\NotBlank(message: 'Password is required.')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long.'
        )]
        public string $password,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            isset($payload['email']) && is_string($payload['email']) ? $payload['email'] : '',
            isset($payload['password']) && is_string($payload['password']) ? $payload['password'] : '',
        );
    }
}
