<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use App\Voucher\Domain\Enum\VoucherType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateVoucherTemplateRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required.')]
        public string $name,
        #[Assert\NotBlank(message: 'Voucher template type is required.')]
        #[Assert\Choice(choices: [VoucherType::Amount->value, VoucherType::Usage->value], message: 'Voucher template type must be amount or usage.')]
        public string $type,
        #[Assert\NotBlank(message: 'Title is required.')]
        public string $title,
        #[Assert\NotBlank(message: 'Description is required.')]
        public string $description,
        #[Assert\NotBlank(message: 'HTML template is required.')]
        public string $htmlTemplate,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['name']) && is_string($payload['name']) ? $payload['name'] : '',
            isset($payload['type']) && is_string($payload['type']) ? $payload['type'] : '',
            isset($payload['title']) && is_string($payload['title']) ? $payload['title'] : '',
            isset($payload['description']) && is_string($payload['description']) ? $payload['description'] : '',
            isset($payload['htmlTemplate']) && is_string($payload['htmlTemplate']) ? $payload['htmlTemplate'] : '',
        );
    }
}
