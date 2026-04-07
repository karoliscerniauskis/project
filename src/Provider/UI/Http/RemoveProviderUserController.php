<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Provider\Application\Command\RemoveProviderUser;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveProviderUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/users/{providerUserId}', name: 'api_provider_user_remove', methods: ['DELETE'])]
    public function __invoke(string $providerId, string $providerUserId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(
            new RemoveProviderUser(
                ProviderId::fromString($providerId),
                ProviderUserId::fromString($providerUserId),
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
