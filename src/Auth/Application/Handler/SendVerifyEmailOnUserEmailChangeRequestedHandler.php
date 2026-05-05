<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Application\Url\FrontendUrlCreator;
use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Shared\Application\Email\EmailSender;

final readonly class SendVerifyEmailOnUserEmailChangeRequestedHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(UserEmailChangeRequested $event): void
    {
        $verifyUrl = $this->frontendUrlCreator->verifyEmail($event->getEmailVerificationSlug());
        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'Verify your new email',
            'Click to verify your email: '.$verifyUrl,
        );
    }
}
