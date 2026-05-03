<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\ChangeUserPassword;
use App\Auth\Infrastructure\Security\SecurityUser;
use App\Auth\UI\Http\OpenApi\InvalidCredentialsResponse;
use App\Auth\UI\Http\Request\ChangeUserPasswordRequest;
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

final class ChangeUserPasswordController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/change-password', name: 'api_auth_change_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/change-password',
        description: 'Changes the password for the authenticated user. Requires the current password for verification.',
        summary: 'Change user password',
        security: [['Bearer' => []]],
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ChangeUserPasswordRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Password changed successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required or current password is incorrect.',
        content: new OA\JsonContent(ref: new Model(type: InvalidCredentialsResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
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
