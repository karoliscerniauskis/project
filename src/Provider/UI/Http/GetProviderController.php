<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetProvider;
use App\Provider\Domain\View\ProviderView;
use App\Provider\UI\Http\OpenApi\ProviderNotFoundResponse;
use App\Provider\UI\Http\OpenApi\ProviderResponse;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProviderController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}', name: 'api_provider_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/providers/{providerId}',
        description: 'Returns a single active provider available to the authenticated user.',
        summary: 'Get provider',
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
        description: 'Provider returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: ProviderResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Provider was not found.',
        content: new OA\JsonContent(ref: new Model(type: ProviderNotFoundResponse::class)),
    )]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
        }

        /** @var ProviderView|null $providerView */
        $providerView = $this->queryBus->ask(
            new GetProvider(
                ProviderId::fromString($providerId),
                UserId::fromString($user->getId()),
            ),
        );

        if ($providerView === null) {
            return new JsonResponse([
                'message' => 'Provider not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $providerView->toArray()]);
    }
}
