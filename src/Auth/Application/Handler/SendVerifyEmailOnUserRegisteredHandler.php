<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Url\FrontendUrlCreator;
use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Application\Email\EmailSender;

final readonly class SendVerifyEmailOnUserRegisteredHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(UserRegistered $event): void
    {
        $verifyUrl = $this->frontendUrlCreator->verifyEmail($event->getEmailVerificationSlug());
        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'Verify your email',
            'Click to verify your email: '.$verifyUrl,
        );
    }
}
