<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\RequestUserEmailChange;
use App\Auth\Infrastructure\Security\SecurityUser;
use App\Auth\UI\Http\Request\ChangeUserEmailRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChangeUserEmailController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/change-email', name: 'api_auth_change_email', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ChangeUserEmailRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ChangeUserEmailRequest::class);
        $this->commandBus->dispatch(new RequestUserEmailChange($user->getId(), $dto->newEmail));

        return new JsonResponse(status: Response::HTTP_ACCEPTED);
    }
}
