<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ConfigureEmailBreachCheckSettingsRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\Type('bool', message: 'Enabled must be a boolean.')]
        public bool $enabled,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['enabled']) && is_bool($payload['enabled']) && $payload['enabled'],
        );
    }
}
