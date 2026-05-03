<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\CreateProvider;
use App\Provider\UI\Http\OpenApi\ProviderNameAlreadyExistsResponse;
use App\Provider\UI\Http\Request\CreateProviderRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateProviderController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/provider', name: 'api_provider_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/provider',
        description: 'Creates a new provider owned by the authenticated user.',
        summary: 'Create provider',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: CreateProviderRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Provider created successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Provider name is already taken.',
        content: new OA\JsonContent(ref: new Model(type: ProviderNameAlreadyExistsResponse::class)),
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var CreateProviderRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, CreateProviderRequest::class);
        $this->commandBus->dispatch(new CreateProvider($user->getId(), $dto->name));

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
