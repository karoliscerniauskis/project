<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use App\Voucher\Application\Command\ChangeVoucherProvider;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use App\Voucher\UI\Http\Request\ChangeVoucherProviderRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChangeVoucherProviderController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/change-provider', name: 'api_voucher_change_provider', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/vouchers/{voucherId}/change-provider',
        description: 'Changes the provider of a voucher. The voucher must be owned by the authenticated user and the new provider must be linked to the current provider.',
        summary: 'Change voucher provider',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
    )]
    #[OA\Parameter(
        name: 'voucherId',
        description: 'Voucher identifier.',
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
        content: new OA\JsonContent(ref: new Model(type: ChangeVoucherProviderRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Voucher provider changed successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Voucher provider cannot be changed.',
        content: new OA\JsonContent(ref: new Model(type: VoucherAccessDeniedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: 'Validation failed.',
        content: new OA\JsonContent(ref: new Model(type: ApiValidationFailedResponse::class)),
    )]
    public function __invoke(string $voucherId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($voucherId)) {
            throw new InvalidRequestParameterException('voucherId', sprintf('Invalid Voucher "%s".', $voucherId));
        }

        /** @var ChangeVoucherProviderRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ChangeVoucherProviderRequest::class);
        $this->commandBus->dispatch(
            new ChangeVoucherProvider(
                $voucherId,
                $user->getId(),
                $dto->newProviderId,
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
