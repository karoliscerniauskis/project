<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\RequestPasswordReset;
use App\Auth\UI\Http\Request\RequestPasswordResetRequest;
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

final class RequestPasswordResetController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/forgot-password', name: 'api_auth_forgot_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/forgot-password',
        description: 'Requests a password reset link to be sent to the specified email address. For security reasons, this endpoint always returns success even if the email does not exist in the system.',
        summary: 'Request password reset',
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: RequestPasswordResetRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Password reset request processed successfully. If the email exists, a reset link will be sent.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var RequestPasswordResetRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, RequestPasswordResetRequest::class);

        $this->commandBus->dispatch(new RequestPasswordReset($dto->email));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
