<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\VerifyUserEmail;
use App\Shared\Application\Bus\CommandBus;
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
    public function __invoke(string $emailVerificationSlug): JsonResponse
    {
        $this->commandBus->dispatch(new VerifyUserEmail($emailVerificationSlug));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
