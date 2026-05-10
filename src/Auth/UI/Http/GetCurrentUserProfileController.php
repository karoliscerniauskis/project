<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Query\GetUserProfile;
use App\Auth\Domain\View\UserProfileView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCurrentUserProfileController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/me', name: 'api_me_profile', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me',
        description: 'Gets the authenticated user profile information.',
        summary: 'Get current user profile',
        security: [['Bearer' => []]],
        tags: ['Auth'],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'User profile retrieved successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'emailBreachCheckEnabled', type: 'boolean', example: false),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['ROLE_USER']),
            ],
            type: 'object'
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

        /** @var UserProfileView $profile */
        $profile = $this->queryBus->ask(new GetUserProfile($user->getId()));

        return new JsonResponse($profile->toArray());
    }
}
