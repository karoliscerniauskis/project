<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Url;

use App\Provider\Application\Url\FrontendUrlCreator as FrontendUrlCreatorInterface;

final readonly class FrontendUrlCreator implements FrontendUrlCreatorInterface
{
    private const string ACCEPT_PROVIDER_INVITATION_FORMAT = '%s/provider-invitations/%s/accept';
    private const string PROVIDER_FORMAT = '%s/providers/%s';
    private const string ADMIN_PROVIDERS_FORMAT = '%s/admin/providers';

    public function __construct(
        private string $frontendUrl,
    ) {
    }

    public function acceptProviderInvitation(string $slug): string
    {
        return sprintf(
            self::ACCEPT_PROVIDER_INVITATION_FORMAT,
            $this->frontendUrl,
            rawurlencode($slug),
        );
    }

    public function provider(string $providerId): string
    {
        return sprintf(
            self::PROVIDER_FORMAT,
            $this->frontendUrl,
            rawurlencode($providerId),
        );
    }

    public function adminProviders(): string
    {
        return sprintf(
            self::ADMIN_PROVIDERS_FORMAT,
            $this->frontendUrl,
        );
    }
}
