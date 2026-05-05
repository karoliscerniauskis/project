<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Shared\Application\Email\EmailSender;

final readonly class SendProviderInvitationEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private FrontendUrlCreator $frontendUrlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(ProviderInvitationCreated $event): void
    {
        $inviteUrl = $this->frontendUrlCreator->acceptProviderInvitation($event->getSlug());
        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'You are invited to join a provider',
            'Click to accept invitation: '.$inviteUrl,
        );
    }
}
