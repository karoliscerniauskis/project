<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetProviders;
use App\Provider\Domain\View\PaginatedProvidersView;
use App\Provider\UI\Http\OpenApi\ProvidersResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProvidersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers', name: 'api_providers_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/providers',
        description: 'Returns providers available to the authenticated user with pagination and filtering support.',
        summary: 'List providers',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 100),
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'Filter providers by name (case-insensitive partial match)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Providers returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: ProvidersResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $nameFilter = $request->query->get('name');

        /** @var PaginatedProvidersView $providersView */
        $providersView = $this->queryBus->ask(
            new GetProviders(
                UserId::fromString($user->getId()),
                $page,
                $limit,
                is_string($nameFilter) && $nameFilter !== '' ? $nameFilter : null,
            ),
        );

        return new JsonResponse($providersView->toArray());
    }
}
