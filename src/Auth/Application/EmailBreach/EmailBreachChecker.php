<?php

declare(strict_types=1);

namespace App\Auth\Application\EmailBreach;

interface EmailBreachChecker
{
    public function check(string $email): EmailBreachCheckResult;
}
