<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\Handler;

use App\Auth\Application\Handler\SendVerifyEmailOnUserRegisteredHandler;
use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Url\UrlCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SendVerifyEmailOnUserRegisteredHandlerTest extends TestCase
{
    public function testSendsVerifyEmailForUserRegisteredEvent(): void
    {
        $emailFrom = 'no-reply@example.com';
        $email = 'user@example.com';
        $slug = 'slug';
        $verifyUrl = 'https://example.com/api/auth/verify-email/'.$slug;
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $emailSender = $this->createMock(EmailSender::class);
        $urlCreator = $this->createMock(UrlCreator::class);
        $urlCreator->expects(self::once())
            ->method('absolute')
            ->with('api_auth_verify_email', [
                'emailVerificationSlug' => $slug,
            ])
            ->willReturn($verifyUrl);
        $emailSender->expects(self::once())
            ->method('send')
            ->with(
                $emailFrom,
                $email,
                'Verify your email',
                'Click to verify your email: '.$verifyUrl,
            );
        $handler = new SendVerifyEmailOnUserRegisteredHandler(
            $emailSender,
            $urlCreator,
            $emailFrom,
        );
        $handler(new UserRegistered($email, $slug, $occurredOn));
    }
}
