<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Email;

use App\Shared\Application\Email\EmailSender;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final readonly class SymfonyEmailSender implements EmailSender
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
    ) {
    }

    public function send(
        string $from,
        string $to,
        string $subject,
        string $text,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        $mail = new Email()
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->html($this->twig->render('email/base.html.twig', [
                'subject' => $subject,
                'message' => $text,
                'actionUrl' => $actionUrl,
                'actionLabel' => $actionLabel,
            ]));

        $this->mailer->send($mail);
    }
}
