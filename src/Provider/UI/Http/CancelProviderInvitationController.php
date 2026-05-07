<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\CancelProviderInvitation;
use App\Provider\UI\Http\OpenApi\ProviderAccessDeniedResponse;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CancelProviderInvitationController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}/invitations/{email}', name: 'api_provider_invitation_cancel', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/providers/{providerId}/invitations/{email}',
        description: 'Cancels a pending provider invitation by invited email address. The authenticated user must be a provider administrator.',
        summary: 'Cancel provider invitation',
        security: [['Bearer' => []]],
        tags: ['Provider'],
    )]
    #[OA\Parameter(
        name: 'providerId',
        description: 'Provider identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
        ),
    )]
    #[OA\Parameter(
        name: 'email',
        description: 'Invited user email address.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'email',
            example: 'invited.user@example.com',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Invitation cancelled successfully. If the invitation does not exist or is not pending, no content is returned as well.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider administrator permission is required.',
        content: new OA\JsonContent(ref: new Model(type: ProviderAccessDeniedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid provider identifier.',
    )]
    public function __invoke(string $providerId, string $email): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
        }

        $this->commandBus->dispatch(
            new CancelProviderInvitation(
                ProviderId::fromString($providerId),
                $email,
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
