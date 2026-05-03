<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetProviders;
use App\Provider\Domain\View\ProvidersView;
use App\Provider\UI\Http\OpenApi\ProvidersResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        description: 'Returns providers available to the authenticated user.',
        summary: 'List providers',
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
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProvidersView $providersView */
        $providersView = $this->queryBus->ask(
            new GetProviders(UserId::fromString($user->getId())),
        );

        return new JsonResponse(['data' => $providersView->toArray()]);
    }
}
