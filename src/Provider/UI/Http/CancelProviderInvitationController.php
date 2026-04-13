<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\CancelProviderInvitation;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CancelProviderInvitationController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/invitations/{email}', name: 'api_provider_invitation_cancel', methods: ['DELETE'])]
    public function __invoke(string $providerId, string $email): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(
            new CancelProviderInvitation(
                ProviderId::fromString($providerId),
                $email,
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
