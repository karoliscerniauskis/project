<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Shared\UI\Http\OpenApi\ApiValidationFailedResponse;
use App\Voucher\Application\Command\TransferVoucher;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use App\Voucher\UI\Http\Request\TransferVoucherRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransferVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/transfer', name: 'api_voucher_transfer', methods: ['POST'])]
    #[OA\Post(
        path: '/api/vouchers/{voucherId}/transfer',
        description: 'Transfers a voucher to another user by email. The authenticated user must be the current owner.',
        summary: 'Transfer voucher',
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
        content: new OA\JsonContent(ref: new Model(type: TransferVoucherRequest::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Voucher transferred successfully.',
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Voucher cannot be transferred.',
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

        /** @var TransferVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, TransferVoucherRequest::class);
        $this->commandBus->dispatch(
            new TransferVoucher(
                $voucherId,
                $user->getUserIdentifier(),
                $dto->recipientEmail,
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
