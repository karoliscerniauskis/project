<?php

declare(strict_types=1);

namespace App\Auth\Application\Handler;

use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Url\UrlCreator;

final readonly class SendVerifyEmailOnUserEmailChangeRequestedHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private UrlCreator $urlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(UserEmailChangeRequested $event): void
    {
        $verifyUrl = $this->urlCreator->absolute('api_auth_verify_email', [
            'emailVerificationSlug' => $event->getEmailVerificationSlug(),
        ]);

        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'Verify your new email',
            'Click to verify your email: '.$verifyUrl,
        );
    }
}
