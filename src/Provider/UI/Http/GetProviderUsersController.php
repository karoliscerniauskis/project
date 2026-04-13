<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetProviderUsers;
use App\Provider\Domain\View\ProviderUsersView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProviderUsersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/users', name: 'api_provider_users_list', methods: ['GET'])]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProviderUsersView $providerUsersView */
        $providerUsersView = $this->queryBus->ask(
            new GetProviderUsers(
                ProviderId::fromString($providerId),
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(['data' => $providerUsersView->toArray()]);
    }
}
