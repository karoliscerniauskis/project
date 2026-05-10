<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\ResetPassword;
use App\Auth\UI\Http\OpenApi\InvalidPasswordResetTokenResponse;
use App\Auth\UI\Http\Request\ResetPasswordRequest;
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

final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/auth/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/reset-password',
        description: 'Resets the user password using a valid reset token received via email.',
        summary: 'Reset password',
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ResetPasswordRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Password reset successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid or expired reset token.',
        content: new OA\JsonContent(ref: new Model(type: InvalidPasswordResetTokenResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var ResetPasswordRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ResetPasswordRequest::class);

        $this->commandBus->dispatch(new ResetPassword(
            $dto->resetToken,
            $dto->newPassword,
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
