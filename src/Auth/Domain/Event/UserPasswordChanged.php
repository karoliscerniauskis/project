<?php

declare(strict_types=1);

namespace App\Auth\Domain\Event;

use App\Shared\Domain\Event\AbstractDomainEvent;

final readonly class UserPasswordChanged extends AbstractDomainEvent
{
}
