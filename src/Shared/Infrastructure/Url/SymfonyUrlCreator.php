<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Url;

use App\Shared\Application\Url\UrlCreator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class SymfonyUrlCreator implements UrlCreator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<string, scalar|null> $parameters
     */
    public function absolute(string $routeName, array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            $routeName,
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * @param array<string, scalar|null> $parameters
     */
    public function path(string $routeName, array $parameters = []): string
    {
        return $this->urlGenerator->generate(
            $routeName,
            $parameters,
        );
    }
}
