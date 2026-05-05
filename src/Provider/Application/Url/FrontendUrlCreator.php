<?php

declare(strict_types=1);

namespace App\Provider\Application\Url;

interface FrontendUrlCreator
{
    public function acceptProviderInvitation(string $slug): string;

    public function provider(string $providerId): string;

    public function adminProviders(): string;
}
