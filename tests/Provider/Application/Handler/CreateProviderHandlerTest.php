<?php

declare(strict_types=1);

namespace App\Tests\Provider\Application\Handler;

use App\Provider\Application\Command\CreateProvider;
use App\Provider\Application\Handler\CreateProviderHandler;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Event\ProviderCreated;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Application\Outbox\OutboxWriter;
use App\Shared\Application\Transaction\TransactionManager;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateProviderHandlerTest extends TestCase
{
    public function testInvokeCreatesProviderAndAssignsAdminUser(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440000';
        $providerId = '550e8400-e29b-41d4-a716-446655440001';
        $providerUserId = '550e8400-e29b-41d4-a716-446655440002';
        $name = 'Provider';
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $command = new CreateProvider($userId, $name);
        $providerRepository = $this->createMock(ProviderRepository::class);
        $providerUserRepository = $this->createMock(ProviderUserRepository::class);
        $uuidCreator = $this->createMock(UuidCreator::class);
        $transactionManager = $this->createMock(TransactionManager::class);
        $outboxWriter = $this->createMock(OutboxWriter::class);
        $clock = $this->createMock(Clock::class);
        $providerRepository
            ->expects(self::once())
            ->method('existsByName')
            ->with($name)
            ->willReturn(false);
        $uuidCreator
            ->expects(self::exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($providerId, $providerUserId);
        $providerRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(Provider::class))
            ->willReturnCallback(static function (Provider $provider) use ($providerId, $name): void {
                self::assertSame($providerId, $provider->getId()->toString());
                self::assertSame($name, $provider->getName());
                self::assertSame(ProviderStatus::Pending, $provider->getStatus());
            });
        $providerUserRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(ProviderUser::class))
            ->willReturnCallback(static function (ProviderUser $providerUser) use ($providerId, $providerUserId, $userId): void {
                self::assertSame($providerUserId, $providerUser->getId()->toString());
                self::assertSame($providerId, $providerUser->getProviderId()->toString());
                self::assertSame($userId, $providerUser->getUserId()->toString());
                self::assertTrue($providerUser->isAdmin());
            });
        $clock
            ->expects(self::once())
            ->method('now')
            ->willReturn($occurredOn);
        $storeAllCall = 0;
        $outboxWriter
            ->expects(self::exactly(2))
            ->method('storeAll')
            ->willReturnCallback(static function (array $events) use ($providerId, $name, $occurredOn, &$storeAllCall): void {
                if ($storeAllCall === 0) {
                    self::assertCount(1, $events);
                    self::assertInstanceOf(ProviderCreated::class, $events[0]);

                    /** @var ProviderCreated $event */
                    $event = $events[0];

                    self::assertSame($providerId, $event->getProviderId());
                    self::assertSame($name, $event->getProviderName());
                    self::assertSame($occurredOn, $event->getOccurredOn());
                }

                if ($storeAllCall === 1) {
                    self::assertSame([], $events);
                }

                ++$storeAllCall;
            });
        $transactionManager
            ->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(static function (callable $callback): void {
                $callback();
            });
        $handler = new CreateProviderHandler(
            $providerRepository,
            $providerUserRepository,
            $uuidCreator,
            $transactionManager,
            $outboxWriter,
            $clock,
        );
        $handler($command);
    }
}
