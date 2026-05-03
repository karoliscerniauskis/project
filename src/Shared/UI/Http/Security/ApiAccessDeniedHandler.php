<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

final readonly class ApiAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Access denied.',
            'errors' => [
                [
                    'field' => 'authorization',
                    'message' => 'You do not have permission to access this resource.',
                ],
            ],
        ], Response::HTTP_FORBIDDEN);
    }
}
