<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetAdminProviders;
use App\Provider\Domain\View\PaginatedProvidersView;
use App\Provider\UI\Http\OpenApi\ProvidersResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetAdminProvidersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/admin/providers', name: 'api_admin_providers_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/providers',
        description: 'Returns providers visible to the authenticated administrator.',
        summary: 'List providers for admin',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 10),
    )]
    #[OA\Parameter(
        name: 'name',
        description: 'Filter by provider name',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Filter by provider status (active, pending, deactivated)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['active', 'pending', 'deactivated']),
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
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Administrator role is required.',
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
        $statusFilter = $request->query->get('status');

        /** @var PaginatedProvidersView $providers */
        $providers = $this->queryBus->ask(new GetAdminProviders(
            $user->getId(),
            $page,
            $limit,
            $nameFilter,
            $statusFilter,
        ));

        return new JsonResponse($providers->toArray());
    }
}
