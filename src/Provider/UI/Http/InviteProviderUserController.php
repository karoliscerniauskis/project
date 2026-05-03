<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\InviteProviderUser;
use App\Provider\UI\Http\OpenApi\ProviderAccessDeniedResponse;
use App\Provider\UI\Http\Request\InviteProviderUserRequest;
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

final class InviteProviderUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/provider/{providerId}/invite', name: 'api_provider_invite_user', methods: ['POST'])]
    #[OA\Post(
        path: '/api/provider/{providerId}/invite',
        description: 'Invites a user to join the selected provider. The authenticated user must have access to the provider.',
        summary: 'Invite provider user',
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
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: InviteProviderUserRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Provider user invitation created successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider access is required.',
        content: new OA\JsonContent(ref: new Model(type: ProviderAccessDeniedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    public function __invoke(string $providerId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var InviteProviderUserRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, InviteProviderUserRequest::class);
        $this->commandBus->dispatch(new InviteProviderUser(
            $providerId,
            $user->getId(),
            $dto->email,
        ));

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
