<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\AcceptProviderInvitation;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AcceptProviderInvitationController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/provider/invitations/{slug}/accept', name: 'api_provider_accept_invitation', methods: ['POST'])]
    #[OA\Post(
        path: '/api/provider/invitations/{slug}/accept',
        description: 'Accepts a provider invitation using the invitation slug. The authenticated user must match the invitation email.',
        summary: 'Accept provider invitation',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\Parameter(
        name: 'slug',
        description: 'Provider invitation slug.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: 'provider-invitation-slug',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Invitation accepted successfully. If the invitation is invalid or cannot be accepted, no content is returned as well.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    public function __invoke(string $slug): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(new AcceptProviderInvitation(
            $slug,
            $user->getId(),
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
