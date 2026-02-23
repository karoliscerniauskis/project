<?php

declare(strict_types=1);

namespace App\Shared\Application\Url;

interface UrlCreator
{
    /**
     * @param array<string, scalar|null> $parameters
     */
    public function absolute(string $routeName, array $parameters = []): string;

    /**
     * @param array<string, scalar|null> $parameters
     */
    public function path(string $routeName, array $parameters = []): string;
}
