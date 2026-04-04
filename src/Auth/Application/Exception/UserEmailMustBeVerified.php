<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class UserEmailMustBeVerified extends AbstractApiException
{
    private function __construct(string $email)
    {
        parent::__construct(
            'Email is not verified.',
            [self::getError('email', sprintf('Please verify "%s" before logging in.', $email))],
        );
    }

    public static function forEmail(string $email): self
    {
        return new self($email);
    }
}
