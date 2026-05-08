<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use App\Voucher\Domain\Enum\VoucherType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateVoucherRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required.')]
        #[Assert\Email(message: 'Email must be valid.')]
        public string $issuedToEmail,
        #[Assert\NotBlank(message: 'Voucher type is required.')]
        #[Assert\Choice(choices: [VoucherType::Amount->value, VoucherType::Usage->value], message: 'Voucher type must be amount or usage.')]
        public string $type,
        #[Assert\Positive(message: 'Amount must be positive.')]
        public ?int $amount,
        #[Assert\Positive(message: 'Usages must be positive.')]
        public ?int $usages,
        #[Assert\GreaterThan('now', message: 'Expiration date must be in the future.')]
        public ?DateTimeImmutable $expiresAt,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['issuedToEmail']) && is_string($payload['issuedToEmail']) ? $payload['issuedToEmail'] : '',
            isset($payload['type']) && is_string($payload['type']) ? $payload['type'] : '',
            isset($payload['amount']) && is_int($payload['amount']) ? $payload['amount'] : null,
            isset($payload['usages']) && is_int($payload['usages']) ? $payload['usages'] : null,
            isset($payload['expiresAt']) && is_string($payload['expiresAt']) ? new DateTimeImmutable($payload['expiresAt']) : null,
        );
    }
}
