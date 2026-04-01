<?php

declare(strict_types=1);

namespace App\Tests\Provider\Domain\Entity;

use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use PHPUnit\Framework\TestCase;

final class ProviderUserTest extends TestCase
{
    public function testAssignCreatesProviderUserWithExpectedState(): void
    {
        $id = ProviderUserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $role = ProviderUserRole::Member;
        $providerUser = ProviderUser::assign($id, $providerId, $userId, $role);

        self::assertSame($id, $providerUser->getId());
        self::assertSame($providerId, $providerUser->getProviderId());
        self::assertSame($userId, $providerUser->getUserId());
        self::assertSame($role, $providerUser->getRole());
        self::assertFalse($providerUser->isAdmin());
    }

    public function testReconstituteRestoresState(): void
    {
        $id = ProviderUserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $role = ProviderUserRole::Admin;
        $providerUser = ProviderUser::reconstitute($id, $providerId, $userId, $role);

        self::assertSame($id, $providerUser->getId());
        self::assertSame($providerId, $providerUser->getProviderId());
        self::assertSame($userId, $providerUser->getUserId());
        self::assertSame($role, $providerUser->getRole());
        self::assertTrue($providerUser->isAdmin());
    }

    public function testAssignAdminCreatesAdminProviderUser(): void
    {
        $id = ProviderUserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $providerUser = ProviderUser::assignAdmin($id, $providerId, $userId);

        self::assertSame($id, $providerUser->getId());
        self::assertSame($providerId, $providerUser->getProviderId());
        self::assertSame($userId, $providerUser->getUserId());
        self::assertSame(ProviderUserRole::Admin, $providerUser->getRole());
        self::assertTrue($providerUser->isAdmin());
    }

    public function testAssignAdminCreatesMemberProviderUser(): void
    {
        $id = ProviderUserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $providerId = ProviderId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440002');
        $providerUser = ProviderUser::assignMember($id, $providerId, $userId);

        self::assertSame($id, $providerUser->getId());
        self::assertSame($providerId, $providerUser->getProviderId());
        self::assertSame($userId, $providerUser->getUserId());
        self::assertSame(ProviderUserRole::Member, $providerUser->getRole());
    }
}
