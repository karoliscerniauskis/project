<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Url\FrontendUrlCreator;
use App\Auth\Domain\Event\PasswordResetRequested;
use App\Shared\Application\Email\EmailSender;

final readonly class SendPasswordResetEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(PasswordResetRequested $event): void
    {
        $resetUrl = $this->frontendUrlCreator->resetPassword($event->getResetToken());
        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'Reset your password',
            'Click the button below to reset your password. This link will expire in 1 hour.',
            $resetUrl,
            'Reset password',
        );
    }
}
