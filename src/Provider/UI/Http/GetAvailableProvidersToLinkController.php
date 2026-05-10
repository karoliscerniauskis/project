<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetAvailableProvidersToLink;
use App\Provider\Domain\View\AvailableProvidersView;
use App\Provider\UI\Http\OpenApi\ProviderAccessDeniedResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetAvailableProvidersToLinkController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/available-to-link', name: 'api_provider_get_available_to_link', methods: ['GET'])]
    #[OA\Get(
        path: '/api/providers/{providerId}/available-to-link',
        description: 'Gets all providers available to link. Returns only active providers where the user is admin and not already linked.',
        summary: 'Get available providers to link',
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
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Available providers retrieved successfully.',
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
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var AvailableProvidersView $view */
        $view = $this->queryBus->ask(new GetAvailableProvidersToLink(
            $providerId,
            $user->getId(),
        ));

        return new JsonResponse($view->toArray());
    }
}
