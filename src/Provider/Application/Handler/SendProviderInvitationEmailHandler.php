<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Url\UrlCreator;

final readonly class SendProviderInvitationEmailHandler
{
    public function __construct(
        private EmailSender $emailSender,
        private UrlCreator $urlCreator,
        private string $emailFrom,
    ) {
    }

    public function __invoke(ProviderInvitationCreated $event): void
    {
        $inviteUrl = $this->urlCreator->absolute('api_provider_accept_invitation', [
            'slug' => $event->getSlug(),
        ]);
        $this->emailSender->send(
            $this->emailFrom,
            $event->getEmail(),
            'You are invited to join a provider',
            'Click to accept invitation: '.$inviteUrl,
        );
    }
}
