<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\ChangeUserPassword;
use App\Auth\Infrastructure\Security\SecurityUser;
use App\Auth\UI\Http\Request\ChangeUserPasswordRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChangeUserPasswordController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/change-password', name: 'api_auth_change_password', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ChangeUserPasswordRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ChangeUserPasswordRequest::class);

        $this->commandBus->dispatch(new ChangeUserPassword(
            $user->getId(),
            $dto->currentPassword,
            $dto->newPassword,
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
