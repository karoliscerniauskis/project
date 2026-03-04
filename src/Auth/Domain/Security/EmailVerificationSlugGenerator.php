<?php

declare(strict_types=1);

namespace App\Auth\Domain\Security;

interface EmailVerificationSlugGenerator
{
    public function generate(): string;
}
