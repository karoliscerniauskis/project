<?php

declare(strict_types=1);

namespace App\Provider\Domain\Invitation;

enum ProviderInvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
