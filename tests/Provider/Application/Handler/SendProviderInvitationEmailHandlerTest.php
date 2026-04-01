<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Handler\SendProviderInvitationEmailHandler;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Url\UrlCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SendProviderInvitationEmailHandlerTest extends TestCase
{
    public function testInvokeSendsInvitationEmailWithAcceptLink(): void
    {
        $emailFrom = 'noreply@example.com';
        $emailTo = 'user@example.com';
        $slug = 'slug';
        $inviteUrl = 'https://example.com/api/provider/accept/invitation/invite-slug';
        $emailSender = $this->createMock(EmailSender::class);
        $urlCreator = $this->createMock(UrlCreator::class);
        $event = new ProviderInvitationCreated(
            '550e8400-e29b-41d4-a716-446655440001',
            $emailTo,
            $slug,
            new DateTimeImmutable('2020-01-01 00:00:00'),
        );
        $urlCreator
            ->expects(self::once())
            ->method('absolute')
            ->with(
                'api_provider_accept_invitation',
                ['slug' => $slug],
            )
            ->willReturn($inviteUrl);
        $emailSender
            ->expects(self::once())
            ->method('send')
            ->with(
                $emailFrom,
                $emailTo,
                'You are invited to join a provider',
                'Click to accept invitation: '.$inviteUrl,
            );
        $handler = new SendProviderInvitationEmailHandler(
            $emailSender,
            $urlCreator,
            $emailFrom,
        );
        $handler($event);
    }
}
