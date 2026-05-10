<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeVoucherProviderRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Provider ID is required.')]
        #[Assert\Uuid(message: 'Provider ID must be a valid UUID.')]
        public string $newProviderId,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['newProviderId']) && is_string($payload['newProviderId']) ? $payload['newProviderId'] : '',
        );
    }
}
