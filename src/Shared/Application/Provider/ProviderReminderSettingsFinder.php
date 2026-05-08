<?php

declare(strict_types=1);

namespace App\Shared\Application\Provider;

use App\Shared\Domain\Id\ProviderId;

interface ProviderReminderSettingsFinder
{
    public function findByProviderId(ProviderId $providerId): ?ProviderReminderSettings;
}
