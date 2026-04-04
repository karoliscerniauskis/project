<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class UserEmailVerificationLinkInvalid extends AbstractApiException
{
    private function __construct(string $slug)
    {
        parent::__construct(
            'Verification link is invalid.',
            [self::getError('emailVerificationSlug', sprintf('Verification link "%s" is invalid or expired.', $slug))],
        );
    }

    public static function forSlug(string $slug): self
    {
        return new self($slug);
    }
}
