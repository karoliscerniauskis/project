<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\RegisterUser;
use App\Auth\UI\Http\Request\RegisterUserRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RegisterUserController
{
    public function __construct(
        private CommandBus $commandBus,
        private JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/register', name: 'api_auth_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var RegisterUserRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, RegisterUserRequest::class);
        $this->commandBus->dispatch(new RegisterUser($dto->email, $dto->password, ['ROLE_USER']));

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
