<?php

declare(strict_types=1);

namespace App\Shared\Application\Email;

interface EmailSender
{
    public function send(string $from, string $to, string $subject, string $text): void;
}
