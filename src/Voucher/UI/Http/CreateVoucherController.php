<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use App\Voucher\Application\Command\CreateVoucher;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use App\Voucher\UI\Http\Request\CreateVoucherRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
        private readonly UuidCreator $uuidCreator,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers', name: 'api_provider_voucher_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/providers/{providerId}/vouchers',
        description: 'Creates a new voucher for the selected provider. The authenticated user must be a provider member.',
        summary: 'Create voucher',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
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
        content: new OA\JsonContent(ref: new Model(type: CreateVoucherRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Voucher created successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider access is required.',
        content: new OA\JsonContent(ref: new Model(type: VoucherAccessDeniedResponse::class)),
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

        /** @var CreateVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, CreateVoucherRequest::class);
        $voucherId = $this->uuidCreator->create();
        $this->commandBus->dispatch(new CreateVoucher(
            $voucherId,
            $providerId,
            $user->getId(),
            $dto->issuedToEmail,
            $dto->type,
            $dto->amount,
            $dto->usages,
        ));

        return new JsonResponse(['id' => $voucherId], Response::HTTP_CREATED);
    }
}
