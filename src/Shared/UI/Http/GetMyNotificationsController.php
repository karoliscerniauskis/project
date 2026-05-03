<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use App\Shared\Application\Notification\NotificationReader;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
use App\Shared\UI\Http\OpenApi\NotificationsResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyNotificationsController extends AbstractController
{
    public function __construct(
        private readonly NotificationReader $notificationReader,
    ) {
    }

    #[Route('/api/me/notifications', name: 'api_me_notifications_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me/notifications',
        description: 'Returns notifications for the authenticated user.',
        summary: 'List my notifications',
        security: [['Bearer' => []]],
        tags: ['Shared'],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Notifications returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: NotificationsResponse::class)),
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

        $notifications = $this->notificationReader->findByUserId(
            UserId::fromString($user->getId()),
        );

        return new JsonResponse(['data' => $notifications->toArray()]);
    }
}
