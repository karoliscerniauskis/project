<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Command\VerifyUserEmail;

final readonly class VerifyUserEmailHandler
{
    public function __invoke(VerifyUserEmail $command): void
    {
    }
}
