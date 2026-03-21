<?php

declare(strict_types=1);

namespace App\Auth\Domain\Slug;

interface EmailVerificationSlugGenerator
{
    public function generate(): string;
}
