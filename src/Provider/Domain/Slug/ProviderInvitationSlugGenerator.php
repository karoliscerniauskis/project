<?php

declare(strict_types=1);

namespace App\Provider\Domain\Slug;

interface ProviderInvitationSlugGenerator
{
    public function generate(): string;
}
