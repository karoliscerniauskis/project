<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ValidateVoucherRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Code is required.')]
        public string $code,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['code']) && is_string($payload['code']) ? $payload['code'] : '',
        );
    }
}
