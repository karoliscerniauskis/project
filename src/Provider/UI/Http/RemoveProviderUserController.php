<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\RemoveProviderUser;
use App\Provider\UI\Http\OpenApi\ProviderAccessDeniedResponse;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderUserId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveProviderUserController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}/users/{providerUserId}', name: 'api_provider_user_remove', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/providers/{providerId}/users/{providerUserId}',
        description: 'Removes a non-administrator user from the selected provider. The authenticated user must be a provider administrator.',
        summary: 'Remove provider user',
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
        name: 'providerUserId',
        description: 'Provider user identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c368',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Provider user removed successfully.',
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
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid provider or provider user identifier.',
    )]
    public function __invoke(string $providerId, string $providerUserId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
        }

        if (!$this->uuidValidator->isValid($providerUserId)) {
            throw new InvalidRequestParameterException('providerUserId', sprintf('Invalid Provider User "%s".', $providerUserId));
        }

        $this->commandBus->dispatch(
            new RemoveProviderUser(
                ProviderId::fromString($providerId),
                ProviderUserId::fromString($providerUserId),
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
