<?php

declare(strict_types=1);

namespace App\Auth\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class UserEmailMustBeUnique extends AbstractApiException
{
    private function __construct(string $email)
    {
        parent::__construct(
            'Email already exists.',
            [self::getError('email', sprintf('Email "%s" is already registered.', $email))],
        );
    }

    public static function forEmail(string $email): self
    {
        return new self($email);
    }
}
