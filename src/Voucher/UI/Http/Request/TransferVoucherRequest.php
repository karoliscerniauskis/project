<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class TransferVoucherRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'Email must be valid.')]
        public string $recipientEmail,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['recipientEmail']) && is_string($payload['recipientEmail']) ? $payload['recipientEmail'] : '',
        );
    }
}
