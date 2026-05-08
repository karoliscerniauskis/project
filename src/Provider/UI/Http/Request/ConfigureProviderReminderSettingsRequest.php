<?php

declare(strict_types=1);

namespace App\Provider\UI\Http\Request;

use App\Shared\UI\Http\JsonMappableRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ConfigureProviderReminderSettingsRequest implements JsonMappableRequest
{
    public function __construct(
        #[Assert\Positive(message: 'Claim reminder days must be positive.')]
        public ?int $claimReminderAfterDays,
        #[Assert\Positive(message: 'Expiry reminder days must be positive.')]
        public ?int $expiryReminderBeforeDays,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): JsonMappableRequest
    {
        return new self(
            isset($payload['claimReminderAfterDays']) && is_int($payload['claimReminderAfterDays'])
                ? $payload['claimReminderAfterDays']
                : null,
            isset($payload['expiryReminderBeforeDays']) && is_int($payload['expiryReminderBeforeDays'])
                ? $payload['expiryReminderBeforeDays']
                : null,
        );
    }
}
