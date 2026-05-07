<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use App\Shared\Application\Notification\NotificationReader;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use App\Shared\UI\Http\OpenApi\UnreadNotificationsCountResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetUnreadNotificationsCountController extends AbstractController
{
    public function __construct(
        private readonly NotificationReader $notificationReader,
    ) {
    }

    #[Route('/api/me/notifications/unread-count', name: 'api_me_notifications_unread_count', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me/notifications/unread-count',
        description: 'Returns the number of unread notifications for the authenticated user.',
        summary: 'Get unread notifications count',
        security: [['Bearer' => []]],
        tags: ['Shared'],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Unread notifications count returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: UnreadNotificationsCountResponse::class)),
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

        return new JsonResponse([
            'data' => [
                'count' => $this->notificationReader->countUnreadByUserId(
                    UserId::fromString($user->getId()),
                ),
            ],
        ]);
    }
}
