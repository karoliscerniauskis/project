<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\UnlinkProvider;
use App\Provider\UI\Http\OpenApi\ProviderAccessDeniedResponse;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UnlinkProviderController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/link/{linkedProviderId}', name: 'api_provider_unlink', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/providers/{providerId}/link/{linkedProviderId}',
        description: 'Unlinks two providers. Admin role required.',
        summary: 'Unlink providers',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\Parameter(
        name: 'providerId',
        description: 'Provider identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
        ),
    )]
    #[OA\Parameter(
        name: 'linkedProviderId',
        description: 'Linked provider identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c370',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Providers unlinked successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider admin role is required.',
        content: new OA\JsonContent(ref: new Model(type: ProviderAccessDeniedResponse::class)),
    )]
    public function __invoke(string $providerId, string $linkedProviderId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(new UnlinkProvider(
            $providerId,
            $linkedProviderId,
            $user->getId(),
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
