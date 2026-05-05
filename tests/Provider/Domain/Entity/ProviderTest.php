<?php

declare(strict_types=1);

namespace App\Tests\Provider\Domain\Entity;

use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Event\ProviderApproved;
use App\Provider\Domain\Event\ProviderCreated;
use App\Provider\Domain\Status\ProviderStatus;
use App\Shared\Domain\Id\ProviderId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ProviderTest extends TestCase
{
    public function testCreateCreatesProviderWithExpectedState(): void
    {
        $id = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $name = 'Provider';
        $status = ProviderStatus::Pending;
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $provider = Provider::create($id, $name, $status, $occurredOn);

        self::assertSame($id, $provider->getId());
        self::assertSame($name, $provider->getName());
        self::assertSame($status, $provider->getStatus());
        self::assertFalse($provider->isActive());

        $events = $provider->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ProviderCreated::class, $events[0]);

        /** @var ProviderCreated $event */
        $event = $events[0];

        self::assertSame($id->toString(), $event->getProviderId());
        self::assertSame($name, $event->getProviderName());
        self::assertSame($occurredOn, $event->getOccurredOn());
    }

    public function testReconstituteRestoresState(): void
    {
        $id = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $name = 'Provider';
        $status = ProviderStatus::Pending;
        $provider = Provider::reconstitute($id, $name, $status);

        self::assertSame($id, $provider->getId());
        self::assertSame($name, $provider->getName());
        self::assertSame($status, $provider->getStatus());
        self::assertFalse($provider->isActive());
    }

    public function testApproveChangesStatusToActiveAndRecordsEvent(): void
    {
        $id = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $name = 'Provider';
        $provider = Provider::reconstitute($id, $name, ProviderStatus::Pending);
        $occurredOn = new DateTimeImmutable('2020-01-01 00:00:00');
        $provider->approve($occurredOn);

        self::assertSame(ProviderStatus::Active, $provider->getStatus());
        self::assertTrue($provider->isActive());

        $events = $provider->pullDomainEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ProviderApproved::class, $events[0]);
    }
}
