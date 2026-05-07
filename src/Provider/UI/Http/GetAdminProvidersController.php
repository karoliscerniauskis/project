<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetAdminProviders;
use App\Provider\Domain\View\ProvidersView;
use App\Provider\UI\Http\OpenApi\ProvidersResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProvidersView $providers */
        $providers = $this->queryBus->ask(new GetAdminProviders($user->getId()));

        return new JsonResponse(['data' => $providers->toArray()]);
    }
}
