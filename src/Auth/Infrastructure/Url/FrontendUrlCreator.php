<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Url;

use App\Auth\Application\Url\FrontendUrlCreator as FrontendUrlCreatorInterface;

final readonly class FrontendUrlCreator implements FrontendUrlCreatorInterface
{
    private const string VERIFY_EMAIL_FORMAT = '%s/verify-email/%s';

    public function __construct(
        private string $frontendUrl,
    ) {
    }

    public function verifyEmail(string $emailVerificationSlug): string
    {
        return sprintf(
            self::VERIFY_EMAIL_FORMAT,
            $this->frontendUrl,
            rawurlencode($emailVerificationSlug),
        );
    }
}
