<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

final class ProviderUser extends AbstractAggregateRoot
{
    private ProviderUserId $id;
    private ProviderId $providerId;
    private UserId $userId;
    private ProviderUserRole $role;
    private ProviderUserStatus $status;

    private function __construct()
    {
        parent::__construct();
    }

    public static function assign(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
        ProviderUserRole $role,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;
        $self->role = $role;
        $self->status = ProviderUserStatus::Active;

        return $self;
    }

    public static function assignAdmin(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
    ): self {
        return self::assign($id, $providerId, $userId, ProviderUserRole::Admin);
    }

    public static function assignMember(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
    ): self {
        return self::assign($id, $providerId, $userId, ProviderUserRole::Member);
    }

    public static function reconstitute(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
        ProviderUserRole $role,
        ProviderUserStatus $status,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;
        $self->role = $role;
        $self->status = $status;

        return $self;
    }

    public function getId(): ProviderUserId
    {
        return $this->id;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getRole(): ProviderUserRole
    {
        return $this->role;
    }

    public function getStatus(): ProviderUserStatus
    {
        return $this->status;
    }

    public function isAdmin(): bool
    {
        return $this->role === ProviderUserRole::Admin;
    }

    public function remove(): void
    {
        if ($this->status !== ProviderUserStatus::Active) {
            return;
        }

        $this->status = ProviderUserStatus::Removed;
    }

    public function isActive(): bool
    {
        return $this->status === ProviderUserStatus::Active;
    }

    public function isRemoved(): bool
    {
        return $this->status === ProviderUserStatus::Removed;
    }
}
