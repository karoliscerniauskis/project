<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Handler\SendProviderApprovedEmailHandler;
use App\Provider\Application\Url\FrontendUrlCreator;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\User\UserEmailFinder;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class SendProviderApprovedEmailHandlerTest extends TestCase
{
    public function testInvokeSendsApprovalEmailToAllAdminUsers(): void
    {
        $providerUrl = 'https://example.com/providers/550e8400-e29b-41d4-a716-446655440001';
        $providerId = '550e8400-e29b-41d4-a716-446655440001';
        $adminUserId1 = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $adminUserId2 = UserId::fromString('550e8400-e29b-41d4-a716-446655440003');
        $adminEmail1 = 'admin1@example.com';
        $adminEmail2 = 'admin2@example.com';
        $emailFrom = 'noreply@example.com';
        $providerRepository = $this->createMock(ProviderRepository::class);
        $providerUserRepository = $this->createMock(ProviderUserRepository::class);
        $userEmailFinder = $this->createMock(UserEmailFinder::class);
        $emailSender = $this->createMock(EmailSender::class);
        $frontendUrlCreator = $this->createMock(FrontendUrlCreator::class);
        $event = new ProviderApproved($providerId, new DateTimeImmutable('2020-01-01 00:00:00'));
        $provider = Provider::reconstitute(
            ProviderId::fromString($providerId),
            'Provider',
            ProviderStatus::Active,
        );
        $providerRepository
            ->expects(self::once())
            ->method('findById')
            ->with(ProviderId::fromString($providerId))
            ->willReturn($provider);
        $providerUserRepository
            ->expects(self::once())
            ->method('findUserIdsByProviderIdAndRole')
            ->with(
                ProviderId::fromString($providerId),
                ProviderUserRole::Admin,
            )
            ->willReturn([$adminUserId1, $adminUserId2]);
        $userEmailFinder
            ->expects(self::exactly(2))
            ->method('findByUserId')
            ->willReturnCallback(static function (UserId $userId) use ($adminUserId1, $adminUserId2, $adminEmail1, $adminEmail2): ?string {
                if ($userId->equals($adminUserId1)) {
                    return $adminEmail1;
                }

                if ($userId->equals($adminUserId2)) {
                    return $adminEmail2;
                }

                return null;
            });
        $frontendUrlCreator
            ->expects(self::once())
            ->method('provider')
            ->with($providerId)
            ->willReturn($providerUrl);
        $call = 0;
        $emailSender
            ->expects(self::exactly(2))
            ->method('send')
            ->willReturnCallback(static function (
                string $from,
                string $to,
                string $subject,
                string $text,
            ) use ($emailFrom, $adminEmail1, $adminEmail2, &$call, $providerUrl): void {
                ++$call;

                self::assertSame($emailFrom, $from);

                if ($call === 1) {
                    self::assertSame($adminEmail1, $to);
                    self::assertSame('Your provider has been approved', $subject);
                    self::assertSame('Your provider "Provider" has been approved and is now active. View it here: '.$providerUrl, $text);

                    return;
                }

                self::assertSame($adminEmail2, $to);
                self::assertSame('Your provider has been approved', $subject);
                self::assertSame('Your provider "Provider" has been approved and is now active. View it here: '.$providerUrl, $text);
            });
        $handler = new SendProviderApprovedEmailHandler(
            $providerRepository,
            $providerUserRepository,
            $userEmailFinder,
            $emailSender,
            $frontendUrlCreator,
            $emailFrom,
        );
        $handler($event);
    }
}
