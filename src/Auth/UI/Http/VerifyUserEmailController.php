<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\VerifyUserEmail;
use App\Auth\UI\Http\OpenApi\UserEmailVerificationLinkInvalidResponse;
use App\Shared\Application\Bus\CommandBus;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class VerifyUserEmailController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {
    }

    #[Route('/api/auth/verify-email/{emailVerificationSlug}', name: 'api_auth_verify_email', methods: ['GET'])]
    #[OA\Get(
        path: '/api/auth/verify-email/{emailVerificationSlug}',
        description: 'Verifies a user\'s email address using the verification slug sent via email.',
        summary: 'Verify email',
        tags: ['Auth'],
    )]
    #[OA\Parameter(
        name: 'emailVerificationSlug',
        description: 'Email verification slug.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            example: 'email-verification-slug',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Email verified successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Verification link is invalid or expired.',
        content: new OA\JsonContent(ref: new Model(type: UserEmailVerificationLinkInvalidResponse::class)),
    )]
    public function __invoke(string $emailVerificationSlug): JsonResponse
    {
        $this->commandBus->dispatch(new VerifyUserEmail($emailVerificationSlug));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
