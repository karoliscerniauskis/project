<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\DeactivateProvider;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeactivateProviderController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/admin/provider/{providerId}/deactivate', name: 'api_admin_provider_deactivate', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/admin/provider/{providerId}/deactivate',
        description: 'Deactivates a provider. This endpoint is intended for administrators only.',
        summary: 'Deactivate provider',
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
        response: Response::HTTP_NO_CONTENT,
        description: 'Provider deactivated successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Administrator role is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid provider identifier.',
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

        $this->commandBus->dispatch(new DeactivateProvider($providerId, $user->getId()));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
