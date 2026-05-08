<?php

declare(strict_types=1);

namespace App\Shared\Application\Email;

interface EmailSender
{
    public function send(
        string $from,
        string $to,
        string $subject,
        string $text,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void;
}
