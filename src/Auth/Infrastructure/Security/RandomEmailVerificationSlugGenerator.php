<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Security\EmailVerificationSlugGenerator;

final readonly class RandomEmailVerificationSlugGenerator implements EmailVerificationSlugGenerator
{
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }
}
