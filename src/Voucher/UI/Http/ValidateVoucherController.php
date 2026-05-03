<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use App\Voucher\Application\Query\ValidateVoucher;
use App\Voucher\Domain\View\VoucherValidationView;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use App\Voucher\UI\Http\OpenApi\VoucherValidationResponse;
use App\Voucher\UI\Http\Request\ValidateVoucherRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ValidateVoucherController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers/validate', name: 'api_provider_vouchers_validate', methods: ['POST'])]
    #[OA\Post(
        path: '/api/providers/{providerId}/vouchers/validate',
        description: 'Validates a voucher by its code without marking it as used. The authenticated user must be a provider member.',
        summary: 'Validate voucher',
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
        content: new OA\JsonContent(ref: new Model(type: ValidateVoucherRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Voucher validation result returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: VoucherValidationResponse::class)),
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

        /** @var ValidateVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ValidateVoucherRequest::class);

        /** @var VoucherValidationView $voucherValidationView */
        $voucherValidationView = $this->queryBus->ask(
            new ValidateVoucher($providerId, $dto->code),
        );

        return new JsonResponse(['data' => $voucherValidationView->toArray()]);
    }
}
