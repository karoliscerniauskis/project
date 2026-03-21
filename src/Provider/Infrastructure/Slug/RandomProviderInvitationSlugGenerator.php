<?php

declare(strict_types=1);

namespace App\Provider\Infrastructure\Slug;

use App\Provider\Domain\Slug\ProviderInvitationSlugGenerator;
use App\Shared\Infrastructure\Slug\AbstractRandomSlugGenerator;

final readonly class RandomProviderInvitationSlugGenerator extends AbstractRandomSlugGenerator implements ProviderInvitationSlugGenerator
{
}
