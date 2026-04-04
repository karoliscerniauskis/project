<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class UserEmailAlreadyVerified extends AbstractApiException
{
    private function __construct(string $email)
    {
        parent::__construct(
            'Email is already verified.',
            [self::getError('email', sprintf('Email "%s" is already verified.', $email))],
        );
    }

    public static function forEmail(string $email): self
    {
        return new self($email);
    }
}
