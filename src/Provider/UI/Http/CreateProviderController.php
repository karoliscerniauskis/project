<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Provider\Application\Command\CreateProvider;
use App\Provider\UI\Http\Request\CreateProviderRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
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
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var CreateProviderRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, CreateProviderRequest::class);
        $this->commandBus->dispatch(new CreateProvider($user->getId(), $dto->name));

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
