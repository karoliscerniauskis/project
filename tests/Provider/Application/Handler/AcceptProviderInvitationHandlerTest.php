<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Command\AcceptProviderInvitation;
use App\Provider\Application\Handler\AcceptProviderInvitationHandler;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Application\User\UserEmailFinder;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AcceptProviderInvitationHandlerTest extends TestCase
{
    public function testInvokeAcceptsInvitationAndCreatesMemberProviderUser(): void
    {
        $slug = 'slug';
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $invitationId = ProviderInvitationId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $providerUserId = '550e8400-e29b-41d4-a716-446655440003';
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $email = 'user@example.com';
        $providerInvitationRepository = $this->createMock(ProviderInvitationRepository::class);
        $providerRepository = $this->createMock(ProviderRepository::class);
        $providerUserRepository = $this->createMock(ProviderUserRepository::class);
        $userEmailFinder = $this->createMock(UserEmailFinder::class);
        $clock = $this->createMock(Clock::class);
        $uuidCreator = $this->createMock(UuidCreator::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $command = new AcceptProviderInvitation($slug, $userId);
        $invitation = ProviderInvitation::reconstitute(
            $invitationId,
            $providerId,
            $email,
            ProviderUserRole::Member,
            $slug,
            ProviderInvitationStatus::Pending,
            UserId::fromString('550e8400-e29b-41d4-a716-446655440010'),
            null,
            new DateTimeImmutable('2020-01-01 00:00:00'),
            null,
            new DateTimeImmutable('2020-01-08 00:00:00'),
        );
        $provider = Provider::reconstitute(
            $providerId,
            'Provider',
            ProviderStatus::Active,
        );
        $providerInvitationRepository
            ->expects(self::once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($invitation);
        $providerRepository
            ->expects(self::once())
            ->method('findById')
            ->with($providerId)
            ->willReturn($provider);
        $userEmailFinder
            ->expects(self::once())
            ->method('findByUserId')
            ->with(UserId::fromString($userId))
            ->willReturn($email);
        $clock
            ->expects(self::once())
            ->method('now')
            ->willReturn($occurredOn);
        $providerInvitationRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (ProviderInvitation $invitation) use ($userId, $occurredOn): bool {
                $acceptedUserId = $invitation->getAcceptedUserId();

                self::assertSame(ProviderInvitationStatus::Accepted, $invitation->getStatus());
                self::assertNotNull($acceptedUserId);
                self::assertTrue(UserId::fromString($userId)->equals($acceptedUserId));
                self::assertSame($occurredOn, $invitation->getAcceptedAt());

                return true;
            }));
        $uuidCreator
            ->expects(self::once())
            ->method('create')
            ->willReturn($providerUserId);
        $providerUserRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (ProviderUser $providerUser) use ($providerUserId, $providerId, $userId): bool {
                self::assertTrue(ProviderUserId::fromString($providerUserId)->equals($providerUser->getId()));
                self::assertSame($providerId, $providerUser->getProviderId());
                self::assertTrue(UserId::fromString($userId)->equals($providerUser->getUserId()));
                self::assertSame(ProviderUserRole::Member, $providerUser->getRole());

                return true;
            }));
        $outboxWriter
            ->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events): bool {
                self::assertCount(0, $events);

                return true;
            }));
        $transactionManager
            ->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new AcceptProviderInvitationHandler(
            $providerInvitationRepository,
            $providerRepository,
            $providerUserRepository,
            $userEmailFinder,
            $clock,
            $uuidCreator,
            $transactionManager,
            $outboxWriter,
        );
        $handler($command);
    }
}
