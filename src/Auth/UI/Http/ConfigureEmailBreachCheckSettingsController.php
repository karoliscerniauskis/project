<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Command\ConfigureEmailBreachCheckSettings;
use App\Auth\UI\Http\OpenApi\UserNotFoundResponse;
use App\Auth\UI\Http\Request\ConfigureEmailBreachCheckSettingsRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConfigureEmailBreachCheckSettingsController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/me/email-breach-check-settings', name: 'api_me_email_breach_check_settings_configure', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/me/email-breach-check-settings',
        description: 'Configures whether the authenticated user allows periodic email breach checks.',
        summary: 'Configure email breach check settings',
        security: [['Bearer' => []]],
        tags: ['Auth'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ConfigureEmailBreachCheckSettingsRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Email breach check settings configured successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Authenticated user was not found.',
        content: new OA\JsonContent(ref: new Model(type: UserNotFoundResponse::class)),
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ConfigureEmailBreachCheckSettingsRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ConfigureEmailBreachCheckSettingsRequest::class);
        $this->commandBus->dispatch(new ConfigureEmailBreachCheckSettings(
            $user->getId(),
            $dto->enabled,
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
