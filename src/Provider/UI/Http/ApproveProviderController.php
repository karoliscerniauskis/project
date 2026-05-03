<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\ApproveProvider;
use App\Shared\Application\Bus\CommandBus;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ApproveProviderController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    #[Route('/api/admin/provider/{providerId}/approve', name: 'api_admin_provider_approve', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/admin/provider/{providerId}/approve',
        description: 'Approves a provider. This endpoint is intended for administrators only.',
        summary: 'Approve provider',
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
        description: 'Provider approved successfully. If the provider does not exist, no content is returned as well.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Administrator role is required.',
    )]
    public function __invoke(string $providerId): JsonResponse
    {
        $this->commandBus->dispatch(new ApproveProvider($providerId));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
