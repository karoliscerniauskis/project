<?php

declare(strict_types=1);

namespace App\Provider\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;

final class ProviderNameAlreadyExists extends AbstractApiException
{
    private function __construct(string $name)
    {
        parent::__construct(
            'Provider name is already taken.',
            [self::getError('name', sprintf('Provider "%s" already exists.', $name))],
        );
    }

    public static function forName(string $name): self
    {
        return new self($name);
    }
}
