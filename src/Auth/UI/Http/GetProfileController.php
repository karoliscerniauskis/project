<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Query\GetCurrentUser;
use App\Auth\Domain\View\UserProfileView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProfileController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/profile', name: 'api_profile_get', methods: ['GET'])]
    #[OA\Get(
        path: '/api/profile',
        description: 'Returns the current authenticated user profile.',
        summary: 'Get current user profile',
        security: [['Bearer' => []]],
        tags: ['Auth'],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User profile returned successfully.',
        content: new OA\JsonContent(
            required: ['email', 'emailBreachCheckEnabled'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'emailBreachCheckEnabled', type: 'boolean', example: true),
            ],
            type: 'object',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var UserProfileView|null $profile */
        $profile = $this->queryBus->ask(
            new GetCurrentUser(UserId::fromString($user->getId())),
        );

        if ($profile === null) {
            return new JsonResponse(status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($profile->toArray());
    }
}
