<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Email;

use App\Shared\Application\Email\EmailSender;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyEmailSender implements EmailSender
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function send(string $from, string $to, string $subject, string $text): void
    {
        $mail = new Email()
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($text);

        $this->mailer->send($mail);
    }
}
