<?php

declare(strict_types=1);

namespace App\Shared\UI\Http;

use App\Shared\Application\Notification\NotificationMarker;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UserId;
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
