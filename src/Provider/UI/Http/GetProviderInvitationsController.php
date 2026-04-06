<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Provider\Application\Query\GetProviderInvitations;
use App\Provider\Domain\View\ProviderInvitationsView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProviderInvitationsController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/invitations', name: 'api_provider_invitations_list', methods: ['GET'])]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProviderInvitationsView $invitationsView */
        $invitationsView = $this->queryBus->ask(
            new GetProviderInvitations(
                ProviderId::fromString($providerId),
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(['data' => $invitationsView->toArray()]);
    }
}
