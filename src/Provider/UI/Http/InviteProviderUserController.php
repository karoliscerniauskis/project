<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Provider\Application\Command\InviteProviderUser;
use App\Shared\Application\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InviteProviderUserController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    #[Route('/api/provider/{providerId}/invite', name: 'api_provider_invite_user', methods: ['POST'])]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        $invitedByUserId = $user->getId();
        $email = 'kcerniauskis@gmail.com';

        $this->commandBus->dispatch(new InviteProviderUser(
            $providerId,
            $invitedByUserId,
            $email,
        ));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
