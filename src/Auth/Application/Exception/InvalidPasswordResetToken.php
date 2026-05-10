<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class InvalidPasswordResetToken extends AbstractApiException
{
    private function __construct()
    {
        parent::__construct(
            'Invalid or expired password reset token.',
            [self::getError('resetToken', 'The password reset link is invalid or has expired.')],
        );
    }

    public static function create(): self
    {
        return new self();
    }
}
