<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\RequestUserEmailChange;
use App\Auth\Infrastructure\Security\SecurityUser;
use App\Auth\UI\Http\OpenApi\UserEmailAlreadyExistsResponse;
use App\Auth\UI\Http\Request\ChangeUserEmailRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/auth/change-email',
        description: 'Requests an email change for the authenticated user. A verification email will be sent to the new email address.',
        summary: 'Change user email',
        security: [['Bearer' => []]],
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ChangeUserEmailRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Email change request accepted. Verification email sent.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
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
