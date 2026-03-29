<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeUserEmailRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'New email is required.')]
        #[Assert\Email(message: 'New email must be valid.')]
        public string $newEmail,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            isset($payload['newEmail']) && is_string($payload['newEmail']) ? $payload['newEmail'] : '',
        );
    }
}
