<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use App\Shared\Application\Notification\NotificationMarker;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use App\Shared\UI\Http\OpenApi\NotificationNotFoundResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MarkNotificationAsReadController extends AbstractController
{
    public function __construct(
        private readonly NotificationMarker $notificationMarker,
    ) {
    }

    #[Route('/api/me/notifications/{id}/read', name: 'api_me_notifications_read', methods: ['POST'])]
    #[OA\Post(
        path: '/api/me/notifications/{id}/read',
        description: 'Marks a notification as read for the authenticated user.',
        summary: 'Mark notification as read',
        security: [['Bearer' => []]],
        tags: ['Shared'],
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Notification identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Notification marked as read successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Notification not found.',
        content: new OA\JsonContent(ref: new Model(type: NotificationNotFoundResponse::class)),
    )]
    public function __invoke(string $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $marked = $this->notificationMarker->markAsRead(
            $id,
            UserId::fromString($user->getId()),
        );

        if (!$marked) {
            return new JsonResponse(status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
