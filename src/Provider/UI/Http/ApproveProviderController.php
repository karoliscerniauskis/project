<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\ApproveProvider;
use App\Shared\Application\Bus\CommandBus;
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
    public function __invoke(string $providerId): JsonResponse
    {
        $this->commandBus->dispatch(new ApproveProvider($providerId));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
