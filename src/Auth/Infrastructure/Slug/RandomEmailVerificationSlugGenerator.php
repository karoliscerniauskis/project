<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Slug;

use App\Auth\Domain\Slug\EmailVerificationSlugGenerator;
use App\Shared\Infrastructure\Slug\AbstractRandomSlugGenerator;

final readonly class RandomEmailVerificationSlugGenerator extends AbstractRandomSlugGenerator implements EmailVerificationSlugGenerator
{
}
