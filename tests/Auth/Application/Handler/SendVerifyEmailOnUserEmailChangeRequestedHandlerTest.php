<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Handler\SendVerifyEmailOnUserEmailChangeRequestedHandler;
use App\Auth\Application\Url\FrontendUrlCreator;
use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Shared\Application\Email\EmailSender;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SendVerifyEmailOnUserEmailChangeRequestedHandlerTest extends TestCase
{
    public function testInvokeSendsVerifyEmailForRequestedEmailChange(): void
    {
        $emailFrom = 'no-reply@example.com';
        $email = 'user@example.com';
        $slug = 'slug';
        $verifyUrl = 'https://example.com/api/auth/verify-email/'.$slug;
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $emailSender = $this->createMock(EmailSender::class);
        $frontendUrlCreator = $this->createMock(FrontendUrlCreator::class);
        $handler = new SendVerifyEmailOnUserEmailChangeRequestedHandler(
            $emailSender,
            $frontendUrlCreator,
            $emailFrom,
        );
        $frontendUrlCreator->expects(self::once())
            ->method('verifyEmail')
            ->with($slug)
            ->willReturn($verifyUrl);
        $emailSender->expects(self::once())
            ->method('send')
            ->with(
                $emailFrom,
                $email,
                'Verify your new email',
                'Click the button below to verify your email address.',
                $verifyUrl,
                'Verify email',
            );
        $handler(new UserEmailChangeRequested($email, $slug, $occurredOn));
    }
}
