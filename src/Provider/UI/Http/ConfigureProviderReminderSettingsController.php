<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Command\ConfigureProviderReminderSettings;
use App\Provider\UI\Http\Request\ConfigureProviderReminderSettingsRequest;
use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConfigureProviderReminderSettingsController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}/reminder-settings', name: 'api_provider_reminder_settings_configure', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/providers/{providerId}/reminder-settings',
        description: 'Configures voucher reminder settings for the selected provider. The authenticated user must be a provider administrator.',
        summary: 'Configure provider voucher reminder settings',
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
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: ConfigureProviderReminderSettingsRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Provider reminder settings configured successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider administrator role is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid provider identifier.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    public function __invoke(string $providerId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
        }

        /** @var ConfigureProviderReminderSettingsRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ConfigureProviderReminderSettingsRequest::class);

        $this->commandBus->dispatch(new ConfigureProviderReminderSettings(
            $providerId,
            $user->getId(),
            $dto->claimReminderAfterDays,
            $dto->expiryReminderBeforeDays,
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
