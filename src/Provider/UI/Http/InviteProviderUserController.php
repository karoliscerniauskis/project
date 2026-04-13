<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\InviteProviderUser;
use App\Provider\UI\Http\Request\InviteProviderUserRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
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
