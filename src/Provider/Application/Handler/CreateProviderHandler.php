<?php

declare(strict_types=1);

namespace App\Provider\Application\Handler;

use App\Provider\Application\Command\CreateProvider;
use App\Provider\Domain\Entity\Provider;
use App\Provider\Domain\Entity\ProviderUser;
use App\Provider\Domain\Repository\ProviderRepository;
use App\Provider\Domain\Repository\ProviderUserRepository;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidCreator;

final readonly class CreateProviderHandler
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ProviderUserRepository $providerUserRepository,
        private UuidCreator $uuidCreator,
    ) {
    }

    public function __invoke(CreateProvider $command): void
    {
        $userId = UserId::fromString($command->getUserId());

        if ($this->providerUserRepository->findProviderIdByUserId($userId) !== null) {
            return;
        }

        $provider = Provider::create(
            ProviderId::fromString($this->uuidCreator->create()),
            $command->getName(),
            'active',
        );

        $providerUser = ProviderUser::assign(
            ProviderUserId::fromString($this->uuidCreator->create()),
            $provider->getId(),
            $userId,
        );

        $this->providerRepository->save($provider);
        $this->providerUserRepository->save($providerUser);
    }
}
