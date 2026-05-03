<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\RegisterUser;
use App\Auth\UI\Http\OpenApi\UserEmailAlreadyExistsResponse;
use App\Auth\UI\Http\Request\RegisterUserRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/auth/register',
        description: 'Registers a new user account. A verification email will be sent to the provided email address.',
        summary: 'Register user',
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: RegisterUserRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'User registered successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Email already exists.',
        content: new OA\JsonContent(ref: new Model(type: UserEmailAlreadyExistsResponse::class)),
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var RegisterUserRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, RegisterUserRequest::class);
        $this->commandBus->dispatch(new RegisterUser($dto->email, $dto->password, ['ROLE_USER']));

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
