<?php

declare(strict_types=1);

namespace App\Auth\Application\Url;

interface FrontendUrlCreator
{
    public function verifyEmail(string $emailVerificationSlug): string;

    public function resetPassword(string $resetToken): string;
}
