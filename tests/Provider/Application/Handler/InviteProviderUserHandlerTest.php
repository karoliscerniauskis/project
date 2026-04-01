<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Command\InviteProviderUser;
use App\Provider\Application\Handler\InviteProviderUserHandler;
use App\Provider\Domain\Entity\ProviderInvitation;
use App\Provider\Domain\Event\ProviderInvitationCreated;
use App\Provider\Domain\Repository\ProviderInvitationRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Slug\ProviderInvitationSlugGenerator;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class InviteProviderUserHandlerTest extends TestCase
{
    public function testInvokeCreatesProviderInvitation(): void
    {
        $providerId = '550e8400-e29b-41d4-a716-446655440001';
        $invitedByUserId = '550e8400-e29b-41d4-a716-446655440002';
        $invitationId = '550e8400-e29b-41d4-a716-446655440003';
        $slug = 'slug';
        $email = 'user@example.com';
        $createdAt = new DateTimeImmutable('2020-01-01 00:00:00');
        $expiresAt = new DateTimeImmutable('2020-01-08 00:00:00');
        $command = new InviteProviderUser($providerId, $invitedByUserId, $email);
        $providerUserRepository = $this->createMock(ProviderUserRepository::class);
        $providerInvitationRepository = $this->createMock(ProviderInvitationRepository::class);
        $uuidCreator = $this->createMock(UuidCreator::class);
        $clock = $this->createMock(Clock::class);
        $providerInvitationSlugGenerator = $this->createMock(ProviderInvitationSlugGenerator::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $providerUserRepository
            ->expects(self::once())
            ->method('isAdmin')
            ->with(
                ProviderId::fromString($providerId),
                UserId::fromString($invitedByUserId),
            )
            ->willReturn(true);
        $providerInvitationRepository
            ->expects(self::once())
            ->method('existsAcceptedByProviderIdAndEmail')
            ->with(
                ProviderId::fromString($providerId),
                $email,
            )
            ->willReturn(false);
        $providerInvitationRepository
            ->expects(self::once())
            ->method('findPendingByProviderIdAndEmail')
            ->with(
                ProviderId::fromString($providerId),
                $email,
            )
            ->willReturn(null);
        $uuidCreator
            ->expects(self::once())
            ->method('create')
            ->willReturn($invitationId);
        $providerInvitationSlugGenerator
            ->expects(self::once())
            ->method('generate')
            ->willReturn($slug);
        $clock
            ->expects(self::exactly(2))
            ->method('now')
            ->willReturnOnConsecutiveCalls($createdAt, $createdAt);
        $providerInvitationRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (ProviderInvitation $invitation) use ($providerId, $invitedByUserId, $email, $slug, $createdAt, $expiresAt, $invitationId): bool {
                self::assertSame($email, $invitation->getEmail());
                self::assertSame($slug, $invitation->getSlug());
                self::assertSame(ProviderUserRole::Member, $invitation->getRole());
                self::assertEquals($createdAt, $invitation->getCreatedAt());
                self::assertEquals($expiresAt, $invitation->getExpiresAt());
                self::assertTrue($invitation->getId()->equals(ProviderInvitationId::fromString($invitationId)));
                self::assertTrue($invitation->getProviderId()->equals(ProviderId::fromString($providerId)));
                self::assertTrue($invitation->getInvitedByUserId()->equals(UserId::fromString($invitedByUserId)));

                return true;
            }));
        $outboxWriter
            ->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events) use ($invitationId, $email, $slug, $createdAt): bool {
                self::assertCount(1, $events);

                /** @var ProviderInvitationCreated $event */
                $event = $events[0];

                self::assertInstanceOf(ProviderInvitationCreated::class, $event);
                self::assertSame($invitationId, $event->getProviderInvitationId());
                self::assertSame($email, $event->getEmail());
                self::assertSame($slug, $event->getSlug());
                self::assertSame($createdAt, $event->getOccurredOn());

                return true;
            }));
        $transactionManager
            ->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new InviteProviderUserHandler(
            $providerUserRepository,
            $providerInvitationRepository,
            $uuidCreator,
            $clock,
            $providerInvitationSlugGenerator,
            $transactionManager,
            $outboxWriter,
        );
        $handler($command);
    }
}
