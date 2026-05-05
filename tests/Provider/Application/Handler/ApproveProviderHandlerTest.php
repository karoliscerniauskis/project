<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Command\ApproveProvider;
use App\Provider\Application\Handler\ApproveProviderHandler;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Security\AdminRoleChecker;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ApproveProviderHandlerTest extends TestCase
{
    public function testInvokeApprovesProviderAndStoresOutboxEvents(): void
    {
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $providerId = '550e8400-e29b-41d4-a716-446655440000';
        $userId = '550e8400-e29b-41d4-a716-446655440001';
        $name = 'Provider';
        $command = new ApproveProvider($providerId, $userId);
        $provider = Provider::reconstitute(
            ProviderId::fromString($providerId),
            $name,
            ProviderStatus::Pending,
        );
        $providerRepository = $this->createMock(ProviderRepository::class);
        $clock = $this->createMock(Clock::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $adminRoleChecker = $this->createMock(AdminRoleChecker::class);
        $adminRoleChecker
            ->expects(self::once())
            ->method('isAdmin')
            ->with(UserId::fromString($userId))
            ->willReturn(true);
        $providerRepository
            ->expects(self::once())
            ->method('findById')
            ->with(ProviderId::fromString($providerId))
            ->willReturn($provider);
        $providerRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Provider $provider) use ($providerId): bool {
                self::assertSame($providerId, $provider->getId()->toString());
                self::assertTrue($provider->isActive());

                return true;
            }));
        $clock
            ->expects(self::once())
            ->method('now')
            ->willReturn($occurredOn);
        $outboxWriter
            ->expects(self::once())
            ->method('storeAll')
            ->with(self::callback(static function (array $events) use ($providerId, $occurredOn): bool {
                self::assertCount(1, $events);
                self::assertInstanceOf(ProviderApproved::class, $events[0]);

                /** @var ProviderApproved $event */
                $event = $events[0];

                self::assertSame($providerId, $event->getProviderId());
                self::assertSame($occurredOn, $event->getOccurredOn());

                return true;
            }));
        $transactionManager
            ->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new ApproveProviderHandler(
            $providerRepository,
            $clock,
            $transactionManager,
            $outboxWriter,
            $adminRoleChecker,
        );
        $handler($command);
    }
}
