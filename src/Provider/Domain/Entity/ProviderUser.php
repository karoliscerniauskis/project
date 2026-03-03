<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;

final class ProviderUser extends AbstractAggregateRoot
{
    private ProviderUserId $id;
    private ProviderId $providerId;
    private UserId $userId;

    private function __construct()
    {
        parent::__construct();
    }

    public static function assign(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;

        return $self;
    }

    public static function reconstitute(
        ProviderUserId $id,
        ProviderId $providerId,
        UserId $userId,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->userId = $userId;

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
}
